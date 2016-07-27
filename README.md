# BearyChat for PHP

[![Latest Stable Version](https://poser.pugx.org/elfsundae/bearychat/version)](https://packagist.org/packages/elfsundae/bearychat)
[![Latest Unstable Version](https://poser.pugx.org/elfsundae/bearychat/v/unstable)](https://packagist.org/packages/elfsundae/bearychat)
[![Total Downloads](https://poser.pugx.org/elfsundae/bearychat/downloads)](https://packagist.org/packages/elfsundae/bearychat)
[![License](https://poser.pugx.org/elfsundae/bearychat/license)](https://packagist.org/packages/elfsundae/bearychat)

A PHP package for sending message to the [BearyChat][] with the [Incoming Webhook][1], and creating response payload for the [Outgoing Robot][2].

+ :cn: [**中文文档**](README_zh.md)
+ **Laravel integration:** [BearyChat for Laravel][Laravel-BearyChat]
+ **Yii integration:** [BearyChat for Yii 2][Yii2-BearyChat]

## Installation

You can install this package using the [Composer][] manager.
```
composer require elfsundae/bearychat
```

Then you may create an Incoming Robot on your [BearyChat][] team account, and read the [payload format][1].

## Documentation

### Overview

To send messages, first create a [BearyChat client](src/Client.php) with your webhook URL:

```php
$client = new \ElfSundae\BearyChat\Client('http://hook.bearychat.com/=.../incoming/...');
```

Besides the webhook, you may want to setup some default values for all messages which will be sent with this client:

```php
use ElfSundae\BearyChat\Client;

$client = new Client($webhook, [
    'channel' => 'server-log',
    'attachment_color' => '#3e4787'
]);
```

All defaults keys are listed in [`MessageDefaults`](src/MessageDefaults.php) . You can access message default with `$client->getMessageDefaults($key)`, or retrieve all defaults with `$client->getMessageDefaults()` .

To send a message, just call `sendMessage` on the client instance with a [message payload][1] array or a payload JSON string:

```php
$client->sendMessage([
    'text' => 'Hi, Elf!',
    'user' => 'elf'
]);

$json = '{"text": "Good job :+1:", "channel": "all"}';
$client->sendMessage($json);
```

In addition to the ugly payload, `sendMessage` can handle `JsonSerializable` instances or any object which provides a payload via its `toArray` or `toJson` method. And there is a ready-made [`Message`](src/Message.php) class available for creating payloads for Incoming messages or Outgoing responses. There are a variety of convenient methods that can work with the payload in [`Message`](src/Message.php) class.

For convenience, any unhandled methods called to a `Client` instance will be sent to a new `Message` instance, and the most methods of a `Message` instance return itself, so you can chain [message modifications](#message-modifications) to achieve one-liner code.

You can also call the powerful `send` or `sendTo` method with message contents for [sending a message](#sending-message).

```php
$client->to('#all')->text('Hello')->add('World')->send();

$client->sendTo('all', 'Hello', 'World');
```

### Message Modifications

Available methods for message modification in the `Message` class:

+ **text**: `getText` , `setText($text)` , `text($text)`
+ **notification**: `getNotification` , `setNotification($notification)` , `notification($notification)`
+ **markdown**: `getMarkdown` , `setMarkdown($markdown)` , `markdown($markdown = true)`
+ **channel**: `getChannel` , `setChannel($channel)` , `channel($channel)` , `to($channel)`
+ **user**: `getUser` , `setUser($user)` , `user($user)` , `to('@'.$user)`
+ **attachments**: `getAttachments` , `setAttachments($attachments)` , `attachments($attachments)` , `addAttachment(...)` , `add(...)` , `removeAttachments(...)` , `remove(...)`

As you can see, the `to($target)` method can change the message's target to an user if `$target` is started with `@` , otherwise it will set the channel that the message should be sent to. The channel's starter mark `#` is **optional** in `to` method, which means the result of `to('#dev')` and `to('dev')` is the same.

Method `addAttachment($attachment)` accepts a PHP array of attachment payload, or a variable arguments list in order of `text, title, images, color`, and the `images` can be an image URL or an array contains image URLs. And this type of attachment parameters is also applicable to the method `add`.

```php
$client->to('@elf')
->text('message')
->add([
    'text' => 'Content of the first attachment.',
    'title' => 'First Attachment',
    'images' => [
        ['url' => $imageUrl],
        ['url' => $imageUrl2]
    ],
    'color' => '#10e4fe'
])
->add(
    'Content of the second attachment.',
    'Second Attachment',
    [$imageUrl, $imageUrl2],
    'red'
)
->send();
```

To remove attachments, call `removeAttachments` or `remove` with indices.

```php
$message->remove(0)->remove(0, 1)->remove([1, 3])->remove();
```

### Message Representation

Call the `toArray()` method on a Message instance will get the payload array for this message. You may use `$message->toJson()`, `json_encode($message)` or `(string) $message` to get the JSON payload for `$message`. 

> :warning: **The message payload may be used for requesting an [Incoming Webhook][1] or creating response for an [Outgoing Robot][2].**

```php
$message = $client->to('@elf')->text('foo')->markdown(false)
    ->add('bar', 'some images', 'path/to/image', 'blue');

echo $message->toJson(JSON_PRETTY_PRINT);
```

The above example will output:

```json
{
    "text": "foo",
    "markdown": false,
    "user": "elf",
    "attachments": [
        {
            "text": "bar",
            "title": "some images",
            "images": [
                {
                    "url": "path\/to\/image"
                }
            ],
            "color": "blue"
        }
    ]
}
```

### Sending Message

You can call `send` or `sendTo` method on a Message instance to send that message.

The `send` method optional accepts variable number of arguments to quickly change the payload content:

+ Sending a basic message: `send($text, $markdown = true, $notification)`
+ Sending a message with one attachment added: `send($text, $attachment_text, $attachment_title, $attachment_images, $attachment_color)`

The `sendTo` method is useful when you want to change the message's target before calling `send` method.

```php
$client = new Client($webhook, [
    'channel' => 'all'
]);

// Sending a message to the default channel
$client->send('Hi there :smile:');

// Sending a customized message
$client->send('disable **markdown**', false, 'custom notification');

// Sending a message with one attachment added
$client->send('message title', 'Attachment Content');

// Sending a message with an customized attachment
$client->send(
    'message with an customized attachment',
    'Attachment Content',
    'Attachment Title',
    $imageUrl,
    '#f00'
);

// Sending a message with multiple images
$client->send('multiple images', null, null, [$imageUrl1, $imageUrl2]);

// Sending a message to a different channel
$client->sendTo('iOS', '**Lunch Time !!!**');

// Sending a message to an user
$client->sendTo('@elf', 'Where are you?');
```

### Customize Client

If you want to create a `Message` instance explicitly, the client's `createMessage` method will return a fresh `Message` instance configured with the client's message defaults.

A `Client` instance is mutable, it means you can change its webhook URL or the message defaults by calling `setWebhook`, `webhook` or `setMessageDefaults`.

```php
$client->webhook($webhook_ios)->setMessageDefaults([
    'channel' => 'ios_dev'
])->send('App reviewing status has updated.');
```

## License

The BearyChat PHP package is available under the [MIT license](LICENSE).

[1]: https://bearychat.com/integrations/incoming
[2]: https://bearychat.com/integrations/outgoing
[BearyChat]: https://bearychat.com
[Composer]: https://getcomposer.org
[Laravel-BearyChat]: https://github.com/ElfSundae/Laravel-BearyChat
[Yii2-BearyChat]: https://github.com/krissss/yii2-beary-chart
