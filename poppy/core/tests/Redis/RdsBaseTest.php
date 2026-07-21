<?php

declare(strict_types = 1);

namespace Poppy\Core\Tests\Redis;

use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Application\TestCase;

class RdsBaseTest extends TestCase
{
    /**
     * Redis Client
     */
    protected RdsDb $rds;

    public function setUp(): void
    {
        parent::setUp();
        $this->rds = sys_tag('py-core:testing');
    }

    /**
     * 测试缓存KEY
     */
    protected function key(string $key): string
    {
        return 'rds-' . $key;
    }
}
