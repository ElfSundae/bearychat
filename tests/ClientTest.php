<?php

namespace ElfSundae\BearyChat\Test;

use ElfSundae\BearyChat\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(Client::class, new Client);
    }

    public function testSetWebhook()
    {
        $client = new Client('foo');
        $this->assertEquals('foo', $client->getWebhook());

        $client->webhook('bar');
        $this->assertEquals('bar', $client->getWebhook());
    }

    public function testSetMessageDefaults()
    {
        $client = new Client(null, ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $client->getMessageDefaults());

        $client->setMessageDefaults(['test' => 'demo']);
        $this->assertEquals(['test' => 'demo'], $client->getMessageDefaults());
    }

    public function testGetMessageDefaultsWithKey()
    {
        $client = new Client;
        $client->setMessageDefaults(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3], $client->getMessageDefaults());
        $this->assertSame(1, $client->getMessageDefaults('a'));
        $this->assertSame(null, $client->getMessageDefaults('not found'));
    }

    public function testCreateMessage()
    {
        $client = new Client;
        $message = $client->createMessage();
        $this->assertSame($client, $message->getClient());
    }
}
