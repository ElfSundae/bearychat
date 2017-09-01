<?php

namespace ElfSundae\BearyChat\Test;

use Exception;
use Mockery as m;
use ElfSundae\BearyChat\Client;
use PHPUnit\Framework\TestCase;
use ElfSundae\BearyChat\Message;
use GuzzleHttp\Client as HttpClient;

class ClientTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

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
        $client->setMessageDefaults(null);
        $this->assertEquals([], $client->getMessageDefaults());

        $client = new Client;
        $this->assertEquals([], $client->getMessageDefaults());

        $client = new Client(null, null);
        $this->assertEquals([], $client->getMessageDefaults());
    }

    public function testSetHttpClient()
    {
        $httpClient = new HttpClient;
        $client = new Client(null, null, $httpClient);
        $this->assertSame($httpClient, $client->getHttpClient());
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

    public function testSendMessageWithWrongParamter()
    {
        $client = new Client;
        $this->assertFalse($client->sendMessage('foobar'));
    }

    public function testSendMessage()
    {
        $httpClient = m::mock(HttpClient::class)
            ->shouldReceive('post')
            ->once()
            ->andThrow(MyException::class)
            ->mock();

        $client = new Client('fake:://webhook', [], $httpClient);
        $this->assertSame($httpClient, $client->getHttpClient());

        $this->expectException(MyException::class);
        $client->sendMessage(json_encode(['text' => 'msg']));
    }

    public function testDynamicCall()
    {
        $client = new Client;
        $message = $client->text('foo');
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($client, $message->getClient());
    }
}

class MyException extends Exception
{
}
