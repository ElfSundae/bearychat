BearyChat for PHP
---

A PHP package for sending message to [BearyChat](https://bearychat.com)
with the [Incoming Webhook](https://bearychat.com/integrations/incoming).

## Installation

You can install this package using the [Composer](https://getcomposer.org) manager.

    composer require elfsundae/bearychat

Then you may create an incoming webhook on your BearyChat team account, and read
the [payload format](https://bearychat.com/integrations/incoming).

## Usage

To send messages, first create a BearyChat client with your webhook URL:

```php
$client = new \ElfSundae\BearyChat\Client('http://hook.bearychat.com/=.../incoming/...');
```

Besides the webhook, you can setup some default values for all messages which will be sent 
with this client:

```php
$client = new Client($webhook, [
    'channel' => 'server-log',
    'attachment_color' => '#3e4787'
]);
```

All defaults keys are listed in [`MessageDefaults`](src/MessageDefaults.php).
You can access message default with `$client->getMessageDefaults($key)`, or retrieve all
defaults with `$client->getMessageDefaults()`.

To send a message, just call `sendMessage` with [message payload](https://bearychat.com/integrations/incoming):

```php
$client->sendMessage([
    'text' => 'Hi, Elf!',
    'user' => 'elf'
]);
```

In addition to the payload, there are a variety of convenient methods that work with the
payload in [`Message`](src/Message.php) class. Any unhandled methods in `Client` will be
sent to a new `Message` instance. And the most of `Message` methods return `Message` itself,
so you can chain message modifications.

```
$client = new Client($webhook);

$client->toChannel('all')
->setText('Hi there!')
->disableMarkdown()
->addAttachment([
    'title' => 'Attatchment Title',
    'images' => [
        ['url' => 'http://loremflickr.com/300/300/cat'],
        ['url' => 'http://loremflickr.com/320/200/dog']
    ]
])
->add([
    'text' => 'Attatchment content'
])
->send();
```

### Message Modifications

+ **text**: `getText`, `setText($text)`
+ **notification**: `getNotification`, `setNotification($notification)`
+ **markdown**: `getMarkdown`, `setMarkdown($markdown)`, `enableMarkdown($enable = true)`, `disableMarkdown`
+ **channel**: `getChannel`, `setChannel($channel)`, `toChannel($channel)`, `to($channel)`
+ **user**: `getUser`, `setUser($user)`, `toUser($user)`, `to('@'.$user)`
+ **attachments**: `getAttachments`, `setAttachments($attachments)`, `addAttachment(...)`, `add(...)`, `removeAttachments(...)`, `remove(...)`

As you can see, the `to($target)` method can change the message's target to an user if
`$target` is started with `@`, otherwise it will set the channel that the message should
be sent to. The channel's starter mark `#` is optional in `to` method, which means the result
of `to('#dev')` and `to('dev')` is the same.

`setAttachments` accepts an array of attachments, and each attachment can be an array
(attachment payload) or a variable arguments list in order of `text`, `title`, `images`
then `color`, and the `images` can be an image URL or an array contains image URLs.
This attachment parameter is also applicable to method `addAttachment` or `add`.

```php
$client->to('@elf')
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

Use `removeAttachments` or `remove` to remove attachment[s]:

```php
$message->remove(0)
->remove(0, 1)
->remove([1, 3]);
```

### Message Presentation

`$message->toArray()` will create an payload array.

### Sending Message

You can call `send` method on a `Message` instance to send that message.
The `send` method optional accepts variable number of arguments to quickly change the
payload content.

+ Sending a basic message: `send($text, $markdown = true, $notification)`
+ Sending a message with one attachment: `send($text, $attachment_text, $attachment_title, $attachment_images, $attachment_color)`

```php
$client = new Client($webhook, [
    'channel' => 'all'
]);

// Sending a message to the default channel
$client->send('(1) Hi there :smile:');

// Sending a customized message
$client->send('(2) disable **markdown**', false, 'custom notification');

// Sending a message with one attachment
$client->send('(3) message title', 'Message Content');

// Sending a message with an customized attachment
$client->send(
    '(4) **message with attachment**',
    'This is an `attachment`.',
    'Attachment Title',
    $imageUrl,
    '#f00'
);

// Sending a message with multiple images
$client->send('(5) New images', null, null, [$imageUrl1, $imageUrl2]);
```

## License

The BearyChat PHP package is available under the [MIT license](LICENSE).
