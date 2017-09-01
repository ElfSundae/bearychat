<?php

namespace ElfSundae\BearyChat\Test;

use PHPUnit\Framework\TestCase;
use ElfSundae\BearyChat\MessageDefaults;

class MessageDefaultsTest extends TestCase
{
    public function testAllKeys()
    {
        $this->assertEquals([
            'channel', 'user', 'markdown', 'notification',
            'attachment_color',
        ], MessageDefaults::allKeys());
    }
}
