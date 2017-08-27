<?php

require __DIR__.'/../vendor/autoload.php';

use ElfSundae\BearyChat\Client;

$webhook = 'https://hook.bearychat.com/=...';
$image1 = 'http://d.hiphotos.baidu.com/image/pic/item/adaf2edda3cc7cd91ededc1a3001213fb90e9106.jpg';

$client = new Client($webhook);

$client->send('hello to default target');

$client
    ->to('@elf')
    ->text('Hello, all')
    ->add('This is an attachment')
    ->addImage($image1, 'Image Description')
    ->send();

$client->sendTo('all',
    '**markdown** content',
    'attachment content',
    'attachment title',
    $image1
);
