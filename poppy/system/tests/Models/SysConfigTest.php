<?php

namespace Poppy\System\Tests\Models;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;

class SysConfigTest extends TestCase
{
    /**
     * @throws ApplicationException
     */
    public function testTableExist(): void
    {
        $exist = SysConfig::tableExists((new PamAccount())->getTable());
        $this->assertTrue($exist);

        $tbExists = SysConfig::tableExists($this->faker()->lexify());
        $this->assertFalse($tbExists);
    }

    public function tearDown(): void
    {
        app('poppy.system.setting')->removeNG('py-system::db');
    }
}