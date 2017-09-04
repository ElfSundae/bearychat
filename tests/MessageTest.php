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
        $message = new Message;
        $message->setChannel('foo');
        $this->assertSame('foo', $message->getChannel());
        $this->assertNull($message->getUser());

        $message->setUser('user')->channel('bar');
        $this->assertSame('bar', $message->getChannel());
        $this->assertNull($message->getUser());
    }

    public function testSetUser()
    {
        $message = new Message;
        $message->setUser('user');
        $this->assertSame('user', $message->getUser());
        $this->assertNull($message->getChannel());

        $message->channel('channel')->user('bar');
        $this->assertSame('bar', $message->getUser());
        $this->assertNull($message->getChannel());
    }

    public function testGetTarget()
    {
        $message = new Message;
        $this->assertNull($message->getTarget());
        $this->assertSame('#foo', $message->channel('foo')->getTarget());
        $this->assertSame('@bar', $message->user('bar')->getTarget());
    }

    public function testSetTarget()
    {
        $message = new Message;
        $message->setTarget('@foo');
        $this->assertSame('foo', $message->getUser());
        $this->assertNull($message->getChannel());

        $message->target('#bar');
        $this->assertSame('bar', $message->getChannel());
        $this->assertNull($message->getUser());

        $message->to('#xyz');
        $this->assertSame('xyz', $message->getChannel());
        $this->assertNull($message->getUser());

        $message->user('abc')->to('123');
        $this->assertSame('123', $message->getChannel());
        $this->assertNull($message->getUser());

        $message->user('abc')->to('#456');
        $this->assertSame('456', $message->getChannel());
        $this->assertNull($message->getUser());

        $message->user('abc')->to('@789');
        $this->assertSame('789', $message->getUser());
        $this->assertNull($message->getChannel());

        $message->to('@');
        $this->assertNull($message->getUser());
        $this->assertNull($message->getChannel());

        $message->to('#');
        $this->assertNull($message->getUser());
        $this->assertNull($message->getChannel());
    }

    public function testRemoveTarget()
    {
        $message = new Message;
        $this->assertNull($message->user('foo')->removeTarget()->getUser());
        $this->assertNull($message->channel('foo')->removeTarget()->getChannel());
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

        $message = (new Message)->add(['images' => 'img']);
        $this->assertEquals([['images' => [['url' => 'img']]]], $message->getAttachments());

        $message = (new Message)->add(['images' => ['img', 'img1']]);
        $this->assertEquals([['images' => [['url' => 'img'], ['url' => 'img1']]]], $message->getAttachments());

        $message = (new Message)->add(['images' => ['url' => 'img']]);
        $this->assertEquals([['images' => [['url' => 'img']]]], $message->getAttachments());
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

        $message = (new Message)
            ->setAttachmentDefaults(['foo' => 'bar'])
            ->add('text');
        $message->setAttachmentDefaults(['foo' => 'abc']);
        $this->assertEquals([['text' => 'text', 'foo' => 'bar']], $message->getAttachments());
        $message->setAttachmentDefaults(['color' => '#abc']);
        $this->assertEquals([['text' => 'text', 'foo' => 'bar', 'color' => '#abc']], $message->getAttachments());
    }

    public function testSetAttachments()
    {
        $message = (new Message)->setAttachments([['text' => 'foo']]);
        $this->assertEquals([['text' => 'foo']], $message->getAttachments());

        $attach = [['text' => 'foo'], ['title' => 'title']];
        $message = (new Message)->attachments($attach);
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

    public function testArrayableAndJsonable()
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

        $this->assertInternalType('string', $json = json_encode($message));
        $this->assertEquals($json, $message->toJson());
        $this->assertEquals($json, (string) $message);
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

    public function testConfigureDefaults()
    {
        $message = new Message;
        $message->configureDefaults(['user' => 'elf']);
        $this->assertEquals(['user' => 'elf'], $message->toArray());

        $message->configureDefaults(['user' => 'sundae']);
        $this->assertEquals(['user' => 'elf'], $message->toArray());

        $message->configureDefaults(['user' => 'sundae'], true);
        $this->assertEquals(['user' => 'elf'], $message->toArray());

        $message->configureDefaults(['channel' => 'channel'], true);
        $this->assertEquals(['user' => 'elf'], $message->toArray());

        $message->configureDefaults(['notification' => 'notes']);
        $this->assertEquals(['user' => 'elf'], $message->toArray());

        $message->configureDefaults(['notification' => 'notes'], true);
        $this->assertEquals(['user' => 'elf', 'notification' => 'notes'], $message->toArray());

        $message->add('text', 'title');
        $this->assertEquals([
            'user' => 'elf',
            'notification' => 'notes',
            'attachments' => [[
                'text' => 'text',
                'title' => 'title',
            ]],
        ], $message->toArray());
        $message->configureDefaults(['attachment_color' => '#fff'], true);
        $this->assertEquals([
            'user' => 'elf',
            'notification' => 'notes',
            'attachments' => [[
                'text' => 'text',
                'title' => 'title',
                'color' => '#fff',
            ]],
        ], $message->toArray());
    }

    public function testContent()
    {
        $message = new Message;
        $message->content('text');
        $this->assertEquals('text', $message->getText());

        $message->content('text', false, 'note');
        $this->assertEquals('text', $message->getText());
        $this->assertFalse($message->getMarkdown());
        $this->assertEquals('note', $message->getNotification());

        $message = new Message;
        $message->content('text', 'attachment_text', 'attachment_title', 'image', '#fff');
        $this->assertEquals([
            'text' => 'text',
            'attachments' => [[
                'text' => 'attachment_text',
                'title' => 'attachment_title',
                'images' => [
                    ['url' => 'image'],
                ],
                'color' => '#fff',
            ]],
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

        $message = (new Message)->addImage('foo', 'bar', 'title');
        $this->assertEquals([
            'attachments' => [[
                'text' => 'bar',
                'title' => 'title',
                'images' => [['url' => 'foo']],
            ]],
        ], $message->toArray());
    }

    public function testSetClient()
    {
        $message = new Message;
        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->once()
            ->andReturn(['user' => 'elf'])
            ->mock();
        $message->setClient($client);
        $this->assertSame($client, $message->getClient());
        $this->assertSame('elf', $message->getUser());

        $message->setClient(null);
        $this->assertNull($message->getClient());

        $client = m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->once()
            ->andReturn(['user' => 'sundae', 'markdown' => false])
            ->mock();
        $message->setClient($client);
        $this->assertSame($client, $message->getClient());
        $this->assertSame('elf', $message->getUser());
        $this->assertSame(false, $message->getMarkdown());
    }

    protected function getClient($defaults = ['user' => 'elf', 'notification' => 'noti', 'attachment_color' => '#f00'])
    {
        return m::mock(Client::class)
            ->shouldReceive('getMessageDefaults')
            ->andReturn($defaults);
    }
}
