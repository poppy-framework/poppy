<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes\Sender;

use AlibabaCloud\SDK\Push\V20160801\Push;
use Poppy\AliyunPush\Classes\Config\Config;
use Poppy\Framework\Classes\Traits\AppTrait;

/**
 * @url https://help.aliyun.com/document_detail/30082.html
 */
abstract class BaseClient
{
    use AppTrait;

    protected string $iosAppKey;

    protected string $androidChannel;

    protected string $androidAppKey;

    /**
     * Aliyun Access Key
     */
    protected string $accessKey;

    /**
     * Aliyun Access Secret
     */
    protected string $accessSecret;

    protected string $androidActivity;

    protected string $clientName = '';

    /**
     * 执行结果
     */
    protected $result;

    /**
     * SendBase constructor.
     */
    public function __construct(Config $config)
    {
        $this->androidAppKey   = $config->getAndroidAppKey();
        $this->iosAppKey       = $config->getIosAppKey();
        $this->androidChannel  = $config->getAndroidChannel();
        $this->androidActivity = $config->getAndroidActivity();
        $this->accessKey       = $config->getAccessKey();
        $this->accessSecret    = $config->getAccessSecret();
        $this->clientName      = $config->getClientName();
    }

    public function setAppConfig($ak, $sk, $android_app_id, $android_channel = '', $ios_key = ''): self
    {
        $this->accessKey      = $ak;
        $this->accessSecret   = $sk;
        $this->androidAppKey  = $android_app_id;
        $this->androidChannel = $android_channel;
        $this->iosAppKey      = $ios_key;

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * 初始化
     */
    protected function initClient(): Push
    {
        $config           = new \Darabonba\OpenApi\Models\Config([
            // 必填，您的 AccessKey ID
            'accessKeyId'     => $this->accessKey,
            // 必填，您的 AccessKey Secret
            'accessKeySecret' => $this->accessSecret,
        ]);
        $config->endpoint = 'cloudpush.aliyuncs.com';

        return new Push($config);
    }
}
