<?php

namespace Demo\Tests\Classes;


use Demo\Classes\Stub;
use Poppy\System\Tests\Base\SystemTestCase;

class StubTest extends SystemTestCase
{


    public function testDoSomething()
    {
        $stub = $this->createStub(Stub::class);
        $stub->method('doSomeThing')
            ->willReturn('stub-testing');

        $this->assertEquals('stub-testing', $stub->doSomeThing());
    }
}
