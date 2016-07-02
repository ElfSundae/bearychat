BearyChat for PHP
---

A PHP package for sending message to [BearyChat](https://bearychat.com) with the [Incoming Webhook](https://bearychat.com/integrations/incoming).

## Installation

You can install this package using the [Composer](https://getcomposer.org) manager.

    composer require elfsundae/bearychat

Then you may create an incoming webhook on your BearyChat team account, and read the [payload format](https://bearychat.com/integrations/incoming).

## Basic Usage

**Sending a message**

```php
$client = new \ElfSundae\BearyChat\Client('http://hook.bearychat.com/=.../incoming/...');

// Sending a message to the default channel
$client->send('Hi there :smile:');

// Sending a styled message
$client->send('disable **markdown**', false, 'custom notification');

// Sending a message with an attachment
$client->send('**message with attachment**', 'This is an `attachment`.', 'Attachment Title', $imageUrl, '#f00');
```

> ![](https://raw.githubusercontent.com/ElfSundae/BearyChat/master/screenshots/sending-a-message.png)


## License

The BearyChat PHP package is available under the [MIT license](LICENSE).
