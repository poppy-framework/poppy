<?php

declare(strict_types = 1);

namespace Poppy\Core\Tests\Redis;

use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Application\TestCase;

class RdsBaseTest extends TestCase
{
    /**
     * Redis Client
     * @var RdsDb
     */
    protected RdsDb $rds;

    public function setUp(): void
    {
        parent::setUp();
        $this->rds = sys_tag('py-core:testing');
    }

    /**
     * 测试缓存KEY
     * @param string $key
     * @return string
     */
    protected function key(string $key): string
    {
        return 'rds-' . $key;
    }
}