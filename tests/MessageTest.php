<?php

namespace ElfSundae\BearyChat\Test;

use ElfSundae\BearyChat\Message;

class MessageTest extends TestCase
{
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
        $this->assertSame([['text' => '123']], $message->getAttachments());

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
}
