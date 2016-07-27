# BearyChat for PHP

[![Latest Stable Version](https://poser.pugx.org/elfsundae/bearychat/version)](https://packagist.org/packages/elfsundae/bearychat)
[![Latest Unstable Version](https://poser.pugx.org/elfsundae/bearychat/v/unstable)](https://packagist.org/packages/elfsundae/bearychat)
[![Total Downloads](https://poser.pugx.org/elfsundae/bearychat/downloads)](https://packagist.org/packages/elfsundae/bearychat)
[![License](https://poser.pugx.org/elfsundae/bearychat/license)](https://packagist.org/packages/elfsundae/bearychat)

这是一个用于向 [BearyChat][] 发送 [Incoming][1] 消息、创建 [Outgoing][2] 响应的 PHP 扩展包。

+ :us: [**Documentation in English**](README.md)
+ **Laravel 集成:** [BearyChat for Laravel][Laravel-BearyChat]
+ **Yii 集成:** [BearyChat for Yii 2][Yii2-BearyChat]

## 安装

你可以使用 [Composer][] 安装此扩展包：
```
composer require elfsundae/bearychat
```

在你的 [BearyChat][] 团队账号下创建 Incoming 机器人，并阅读其[消息格式][1]。

## 文档

### 概述

要发送消息，首先需要创建一个 [BearyChat client](src/Client.php) ，并在其初始化方法中传入 webhook 的 URL:

```php
$client = new \ElfSundae\BearyChat\Client('http://hook.bearychat.com/=.../incoming/...');
```

除了 webhook URL ，你还可以为所有由这个 client 发出的消息预设一些默认值：

```php
use ElfSundae\BearyChat\Client;

$client = new Client($webhook, [
    'channel' => 'server-log',
    'attachment_color' => '#3e4787'
]);
```

所有支持的消息预设名 (key) 罗列在 [`MessageDefaults`](src/MessageDefaults.php) 类中。可以通过调用 `$client->getMessageDefaults($key)` 获取某个预设值，或者调用 `$client->getMessageDefaults()` （不传参数）来获取所有消息预设值。

要发送一条消息，只需调用 client 的 `sendMessage` 方法并传入[消息 payload][1] 数组或 JSON 字符串:

```php
$client->sendMessage([
    'text' => 'Hi, Elf!',
    'user' => 'elf'
]);

$json = '{"text": "Good job :+1:", "channel": "all"}';
$client->sendMessage($json);
```

除了原生的消息 payload ，`sendMessage` 还可以处理 `JsonSerializable` 实例，或任意通过其 `toArray` 或 `toJson` 方法提供 payload 的对象。同时该扩展包也提供了一个现成的 [`Message`](src/Message.php) 类用于创建 Incoming 消息，或生成 Outgoing 响应。`Message` 类有很多便捷方法用来[操作消息 payload](#编辑消息)。

为了方便使用，对 `Client` 实例的所有不支持的方法调用将被发送至一个新的 `Message` 对象，并且 `Message` 对象的绝大多数方法支持链接调用，这样就可以实现一行代码完成[编辑消息](#编辑消息)和[发送消息](#发送消息)。

另外，`Message` 对象还提供了两个强大的方法 `send` 和 `sendTo` 用来快速实现消息的编辑和发送。

```php
$client->to('#all')->text('Hello')->add('World')->send();

$client->sendTo('all', 'Hello', 'World');
```

### 编辑消息

`Message` 对象可用的编辑消息的方法如下：

+ **text**: `getText` , `setText($text)` , `text($text)`
+ **notification**: `getNotification` , `setNotification($notification)` , `notification($notification)`
+ **markdown**: `getMarkdown` , `setMarkdown($markdown)` , `markdown($markdown = true)`
+ **channel**: `getChannel` , `setChannel($channel)` , `channel($channel)` , `to($channel)`
+ **user**: `getUser` , `setUser($user)` , `user($user)` , `to('@'.$user)`
+ **attachments**: `getAttachments` , `setAttachments($attachments)` , `attachments($attachments)` , `addAttachment(...)` , `add(...)` , `removeAttachments(...)` , `remove(...)`

如你所见，`to($target)` 方法可以改变消息的目标（接收方），如果参数 `$target` 是一个以 `@` 打头的字符串，消息将被发送至某个“人”（ user ），否则消息的目标将是一个“讨论组”（ channel ）。讨论组名字的打头字符 `#` 是可选的，这意味着 `to('#dev')` 和 `to('dev')` 效果一样。

方法 `addAttachment($attachment)` 可接受一个 PHP 数组 (attachment payload) 作为其参数，也可以是一个按照 `text, title, images, color` 顺序的可变参数，并且 `images` 可以是一个图片 URL 字符串也可以是一个包含图片 URL 的数组。`addAttachment` 的这种参数类型同样适应于 `add` 方法。

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

调用 `removeAttachments` 或 `remove` 方法并传入附件索引可以移除某附件。不传参数会移除消息里的所有附件。

```php
$message->remove(0)->remove(0, 1)->remove([1, 3])->remove();
```

### 消息持久化

调用 `Message` 对象的 `toArray()` 方法可以得到这个消息的 payload 数组。也可以使用 `$message->toJson()`, `json_encode($message)` 或 `(string) $message` 得到 `$message` 的 JSON payload.

> :warning: **消息 payload 可以被用来请求 [Incoming Webhook][1] 或响应 [Outgoing Robot][2].**

```php
$message = $client->to('@elf')->text('foo')->markdown(false)
            ->add('bar', 'some images', 'path/to/image', 'blue');

echo $message->toJson(JSON_PRETTY_PRINT);
```

执行上面的代码会输出以下内容：

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

### 发送消息

调用 `Message` 对象的 `send` 或 `sendTo` 方法可以发送这条消息，并且这两个方法也接受可变参数以便快速修改消息内容。

+ 发送一条简单的消息: `send($text, $markdown = true, $notification)`
+ 发送一条带有附件的消息: `send($text, $attachment_text, $attachment_title, $attachment_images, $attachment_color)`

`sendTo` 方法的第一个参数是要发送的目标，其他参数跟 `send` 方法的参数一样。

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

### 定制 Client

如果你想显式创建 `Message` 对象，client 的 `createMessage` 方法会返回一个全新的、带有消息预设值的 `Message` 对象。

`Client` 对象是可变的，这意味着你可以通过调用 `setWebhook` 、`webhook` 或 `setMessageDefaults` 方法来改变 client 的 webhook URL 、消息的预设值等。

```php
$client->webhook($webhook_ios)->setMessageDefaults([
    'channel' => 'ios_dev'
])->send('App reviewing status has updated.');
```

## 许可协议

BearyChat PHP 扩展包在 [MIT 许可协议](LICENSE)下提供和使用。

[1]: https://bearychat.com/integrations/incoming
[2]: https://bearychat.com/integrations/outgoing
[BearyChat]: https://bearychat.com
[Composer]: https://getcomposer.org
[Laravel-BearyChat]: https://github.com/ElfSundae/Laravel-BearyChat
[Yii2-BearyChat]: https://github.com/krissss/yii2-beary-chart
