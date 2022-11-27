<?php

namespace Demo\Tests\Failed;

use Poppy\System\Tests\Base\SystemTestCase;

class AssertTest extends SystemTestCase
{
    public function testAssert()
    {
        $int = 1;
        $this->assertSame('1', $int, 'Int 1 not equal to String `1`');
    }
}
