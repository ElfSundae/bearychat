<?php

namespace ElfSundae\BearyChat\Test;

use Mockery as m;
use ElfSundae\BearyChat\Client;
use PHPUnit\Framework\TestCase;
use ElfSundae\BearyChat\Message;

class MessageTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(Message::class, new Message);
    }

    public function testSetText()
    {
        $this->assertSame('foo', (new Message)->text('foo')->getText());
    }

    public function testSetNotification()
    {
        $this->assertSame('foo', (new Message)->notification('foo')->getNotification());
    }

    public function testSetMarkdown()
    {
        $this->assertFalse((new Message)->markdown(false)->getMarkdown());
        $this->assertTrue((new Message)->markdown(true)->getMarkdown());
    }

    public function testSetChannel()
    {
        $this->assertSame('foo', (new Message)->channel('foo')->getChannel());
    }

    public function testSetUser()
    {
        $this->assertSame('foo', (new Message)->user('foo')->getUser());
    }

    public function testTo()
    {
        $message = new Message;
        $this->assertSame('foo', $message->to('@foo')->getUser());
        $this->assertSame('foo', $message->to('#foo')->getChannel());
        $this->assertSame('foo', $message->to('foo')->getChannel());
        $this->assertNull($message->getUser());
    }

    public function testSetAttachmentDefaults()
    {
        $message = new Message;
        $this->assertEmpty($message->getAttachmentDefaults());
        $message->setAttachmentDefaults(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $message->getAttachmentDefaults());
    }

    public function testAddAttachment()
    {
        $message = new Message;
        $this->assertEmpty($message->getAttachments());

        $message = (new Message)->addAttachment('text');
        $this->assertEquals([['text' => 'text']], $message->getAttachments());

        $message = (new Message)->addAttachment('text', 'title');
        $this->assertEquals([['text' => 'text', 'title' => 'title']], $message->getAttachments());

        $message = (new Message)->addAttachment(null, 'title');
        $this->assertEquals([['title' => 'title']], $message->getAttachments());

        $message = (new Message)->addAttachment('text', 'title', 'path/to/image');
        $this->assertEquals([[
            'text' => 'text',
            'title' => 'title',
            'images' => [['url' => 'path/to/image']],
        ]], $message->getAttachments());
        $message = (new Message)->addAttachment('text', 'title', ['path/to/image']);
        $this->assertEquals([[
            'text' => 'text',
            'title' => 'title',
            'images' => [['url' => 'path/to/image']],
        ]], $message->getAttachments());
        $message = (new Message)->addAttachment('text', 'title', ['url' => 'path/to/image']);
        $this->assertEquals([[
            'text' => 'text',
            'title' => 'title',
            'images' => [['url' => 'path/to/image']],
        ]], $message->getAttachments());
        $message = (new Message)->addAttachment('text', 'title', ['img1', 'img2']);
        $this->assertEquals([[
            'text' => 'text',
            'title' => 'title',
            'images' => [['url' => 'img1'], ['url' => 'img2']],
        ]], $message->getAttachments());

        $message = (new Message)->addAttachment(null, null, null, '#ffeecc');
        $this->assertEquals([['color' => '#ffeecc']], $message->getAttachments());

        $message = (new Message)->addAttachment(['foo' => 'bar']);
        $this->assertEquals([['foo' => 'bar']], $message->getAttachments());

        $message = (new Message)->addAttachment(123);
        $this->assertEquals([['text' => '123']], $message->getAttachments());

        $message = (new Message)->add(null);
        $this->assertEmpty($message->getAttachments());

        $message = (new Message)->add('foo')->add('bar');
        $this->assertEquals([['text' => 'foo'], ['text' => 'bar']], $message->getAttachments());
    }

    public function testMergeAttachmentDefaults()
    {
        $message = (new Message)->setAttachmentDefaults(['foo' => 'bar']);
        $message->addAttachment('text');
        $this->assertEquals([['text' => 'text', 'foo' => 'bar']], $message->getAttachments());

        $message = (new Message)->setAttachmentDefaults(['text' => 'foo']);
        $message->addAttachment('bar');
        $this->assertEquals([['text' => 'bar']], $message->getAttachments());

        $message = (new Message)->setAttachmentDefaults(['text' => 'foo', 'color' => '#fff']);
        $message->addAttachment('bar');
        $this->assertEquals([['text' => 'bar', 'color' => '#fff']], $message->getAttachments());
    }

    public function testSetAttachments()
    {
        $message = (new Message)->setAttachments([['text' => 'foo']]);
        $this->assertEquals([['text' => 'foo']], $message->getAttachments());

        $attach = [['text' => 'foo'], ['title' => 'title']];
        $message = (new Message)->setAttachments($attach);
        $this->assertEquals($attach, $message->getAttachments());
    }

    public function testRemoveAttachments()
    {
        $message = (new Message)->add('foo')->remove();
        $this->assertEmpty($message->getAttachments());

        $message = (new Message)->add('foo')->add('bar')->remove(0);
        $this->assertEquals([['text' => 'bar']], $message->getAttachments());

        $message = (new Message)->add('foo')->add('bar')->add('xxx')->remove(0, 1);
        $this->assertEquals([['text' => 'xxx']], $message->getAttachments());

        $message = (new Message)->add('foo')->add('bar')->add('xxx')->remove([2]);
        $this->assertEquals([['text' => 'foo'], ['text' => 'bar']], $message->getAttachments());
    }

    public function testArrayable()
    {
        $this->assertInternalType('array', (new Message)->toArray());

        $message = (new Message)->to('@elf')->text('foo')->add('bar');
        $this->assertEquals([
            'user' => 'elf',
            'text' => 'foo',
            'attachments' => [
                ['text' => 'bar'],
            ],
        ], $message->toArray());
    }

    public function testCreateMessageWithDefaultsFromClient()
    {
        $client = $this->getClient()->mock();

        $message = (new Message($client))->text('msg')->add('attach');
        $this->assertEquals('elf', $message->getUser());
        $this->assertEquals('noti', $message->getNotification());
        $this->assertEquals([
            'user' => 'elf',
            'notification' => 'noti',
            'text' => 'msg',
            'attachments' => [
                ['text' => 'attach', 'color' => '#f00'],
            ],
        ], $message->toArray());
    }

    public function testSend()
    {
        $this->assertFalse((new Message)->send());

        $client = $this->getClient()
            ->shouldReceive('sendMessage')
            ->with(m::type(Message::class))
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue((new Message($client))->send());

        $client = $this->getClient()
            ->shouldReceive('sendMessage')
            ->with(['foo' => 'bar'])
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send(['foo' => 'bar']);

        $obj = new \stdClass;
        $client = $this->getClient()
            ->shouldReceive('sendMessage')
            ->with($obj)
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send($obj);

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg'];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg');

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg', 'markdown' => false];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg', false);

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg', 'markdown' => false, 'notification' => 'noti'];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg', false, 'noti');

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg', 'attachments' => [['text' => 'attach']]];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg', 'attach');

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg', 'attachments' => [['text' => 'attach', 'title' => 'attach_title']]];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg', 'attach', 'attach_title');

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg', 'attachments' => [['text' => 'attach', 'title' => 'attach_title', 'images' => [['url' => 'path/to/image']]]]];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg', 'attach', 'attach_title', 'path/to/image');

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['text' => 'msg', 'attachments' => [['text' => 'attach', 'title' => 'attach_title', 'images' => [['url' => 'path/to/image']], 'color' => '#fcc']]];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        (new Message($client))->send('msg', 'attach', 'attach_title', 'path/to/image', '#fcc');
    }

    public function testSendTo()
    {
        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['channel' => 'foo'];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue((new Message($client))->sendTo('foo'));

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['user' => 'elf'];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue((new Message($client))->sendTo('@elf'));

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == [];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue((new Message($client))->sendTo(null));

        $client = $this->getClient([])
            ->shouldReceive('sendMessage')
            ->with(m::on(function ($message) {
                return $message instanceof Message &&
                    $message->toArray() == ['user' => 'elf', 'text' => 'foobar', 'markdown' => false];
            }))
            ->once()
            ->andReturn(true)
            ->mock();
        $this->assertTrue((new Message($client))->sendTo('@elf', 'foobar', false));
    }

    public function testAddImage()
    {
        $message = (new Message)->addImage('path/to/image');
        $this->assertEquals([
            'attachments' => [[
                'images' => [['url' => 'path/to/image']],
            ]],
        ], $message->toArray());

        $message = (new Message)->addImage(['foo', 'bar']);
        $this->assertEquals([
            'attachments' => [[
                'images' => [['url' => 'foo'], ['url' => 'bar']],
            ]],
        ], $message->toArray());

        $message = (new Message)->addImage('foo', 'bar');
        $this->assertEquals([
            'attachments' => [[
                'text' => 'bar',
                'images' => [['url' => 'foo']],
            ]],
        ], $message->toArray());
    }

    protected function getClient($defaults = ['user' => 'elf', 'notification' => 'noti', 'attachment_color' => '#f00'])
    {
        return m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn($defaults);
    }
}
