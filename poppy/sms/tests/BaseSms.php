<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Poppy\Framework\Application\TestCase;

/**
 * 发送短信
 */
class BaseSms extends TestCase
{

    /**
     * 手机
     * @var array|mixed
     */
    protected $mobile;

    /**
     * 配置文件
     * @var array
     */
    protected array $conf;

    public function setUp(): void
    {
        parent::setUp();
        $this->conf   = $this->readJson('poppy.sms', 'tests/config/account.json');
        $this->mobile = data_get($this->conf, 'mobile');
    }
}