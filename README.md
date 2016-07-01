BearyChat for PHP
---

A PHP package for sending message to [BearyChat](https://bearychat.com) with the [Incoming Webhook](https://bearychat.com/integrations/incoming).

## Installation

You can install this package using the [Composer](https://getcomposer.org) manager.

    composer require elfsundae/bearychat

Then you may create an incoming webhook on your BearyChat team account, and read the [payload format](https://bearychat.com/integrations/incoming).

## Usage

```php
$client = new \ElfSundae\BearyChat\Client('http://hook.bearychat.com/=.../incoming/...');

$client->sendMessage([
    'text' => 'Hi there :smile:'
]);

$client->sendMessage([
    'text' => 'another message.',
    'channel' => 'all',
    'attachments' => [
        [
            'title' => 'attach title',
            'text' => 'attach content https://github.com/ElfSundae/BearyChat',
            'color' => 'red'
        ],
        [
            'text' => 'This is an image :sunrise:',
            'images' => [
                ['url' => 'https://bearychat.com/94030a9693952e9f7e769a5c61d2dcfb.png']
            ],
            'color' => '#3e4787'
        ]
    ]
]);
```

## License

The BearyChat PHP package is available under the [MIT license](LICENSE).
