<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Tests;

use Poppy\Framework\Application\TestCase;

/**
 * 发送短信
 */
abstract class BaseAliyun extends TestCase
{
    /**
     * 配置文件
     * @var array
     */
    protected array $conf;
    protected string $confAccessKey;
    protected string $confAccessSecret;
    protected string $confEndpoint;
    protected string $confBucket;
    protected string $confUrlPrefix;
    protected string $confRoleArn;
    protected string $confTempAccessKey;
    protected string $confTempAccessSecret;
    protected string $confWatermark;

    public function setUp(): void
    {
        parent::setUp();
        $this->conf                 = $this->readJson('poppy.aliyun-oss', 'tests/config/account.json');
        $this->confAccessKey        = data_get($this->conf, 'access_key');
        $this->confAccessSecret     = data_get($this->conf, 'access_secret');
        $this->confEndpoint         = data_get($this->conf, 'endpoint');
        $this->confBucket           = data_get($this->conf, 'bucket');
        $this->confUrlPrefix        = data_get($this->conf, 'url_prefix');
        $this->confRoleArn          = data_get($this->conf, 'role_arn');
        $this->confTempAccessKey    = data_get($this->conf, 'temp_access_key');
        $this->confTempAccessSecret = data_get($this->conf, 'temp_access_secret');
        $this->confWatermark        = data_get($this->conf, 'watermark');
    }
}