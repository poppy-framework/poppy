<?php

namespace Poppy\System\Tests\Base;

use InvalidArgumentException;

class ConfigTest extends SystemTestCase
{
    /**
     * 测试存在 Public Storage
     * @return void
     */
    public function testHasPublicStorage()
    {
        try {
            app('filesystem')->disk('public');
            $this->assertTrue(true);
        } catch (InvalidArgumentException $e) {
            $this->fail('disk `public` not exist, you need define `public` directory for local upload');
        }
    }
}