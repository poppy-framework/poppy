<?php

namespace Demo\Tests\Failed;

use Poppy\Framework\Application\TestCase;

class AssertTest extends TestCase
{
    public function testAssert(): void
    {
        $int = 1;
        $this->assertSame('1', $int, 'Int 1 not equal to String `1`');
    }
}
