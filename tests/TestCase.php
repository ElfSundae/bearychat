<?php

namespace ElfSundae\BearyChat\Test;

use Mockery as m;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function tearDown()
    {
        m::close();
    }
}
