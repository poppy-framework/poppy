<?php

namespace Demo\Tests\Classes;

use Demo\Classes\Stub;
use Poppy\Framework\Application\TestCase;

class StubTest extends TestCase
{
    public function testDoSomething(): void
    {
        $stub = $this->createStub(Stub::class);
        $stub->method('doSomeThing')
            ->willReturn('stub-testing');

        $this->assertEquals('stub-testing', $stub->doSomeThing());
    }
}
