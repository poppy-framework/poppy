<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes\Config;

class Config
{
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

    /**
     * 需要打开的页面
     */
    protected string $androidActivity;

    /**
     * 客户端name
     */
    protected string $clientName;

    public function __construct($ak, $sk, $android_app_id, $android_channel = '', $android_activity = '', $ios_key = '', $clientName = '')
    {
        $this->accessKey       = (string) $ak;
        $this->accessSecret    = (string) $sk;
        $this->androidAppKey   = (string) $android_app_id;
        $this->androidChannel  = (string) $android_channel;
        $this->androidActivity = (string) $android_activity;
        $this->iosAppKey       = (string) $ios_key;
        $this->clientName      = trim((string) $clientName);
    }

    public function getIosAppKey(): string
    {
        return $this->iosAppKey;
    }

    public function getAndroidChannel(): string
    {
        return $this->androidChannel;
    }

    public function getAndroidActivity(): string
    {
        return $this->androidActivity;
    }

    public function getAndroidAppKey(): string
    {
        return $this->androidAppKey;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function getAccessSecret(): string
    {
        return $this->accessSecret;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * 默认配置
     *
     * @return static
     */
    public static function default(): self
    {
        $androidAppKey   = config('poppy.aliyun-push.android_app_key');
        $iosAppKey       = config('poppy.aliyun-push.ios_app_key');
        $androidChannel  = config('poppy.aliyun-push.android_channel');
        $accessKey       = config('poppy.aliyun-push.access_key');
        $accessSecret    = config('poppy.aliyun-push.access_secret');
        $androidActivity = config('poppy.aliyun-push.android_activity');

        return new Config($accessKey, $accessSecret, $androidAppKey, $androidChannel, $androidActivity, $iosAppKey);
    }
}
