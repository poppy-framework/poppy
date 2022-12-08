<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes\Config;

class Config
{

    /**
     * @var string
     */
    protected string $iosAppKey;

    /**
     * @var string
     */
    protected string $androidChannel;

    /**
     * @var string
     */
    protected string $androidAppKey;

    /**
     * Aliyun Access Key
     * @var string
     */
    protected string $accessKey;


    /**
     * Aliyun Access Secret
     * @var string
     */
    protected string $accessSecret;

    /**
     * 需要打开的页面
     * @var string
     */
    protected string $androidActivity;

    public function __construct($ak, $sk, $android_app_id, $android_channel = '', $android_activity = '', $ios_key = '')
    {
        $this->accessKey       = (string) $ak;
        $this->accessSecret    = (string) $sk;
        $this->androidAppKey   = (string) $android_app_id;
        $this->androidChannel  = (string) $android_channel;
        $this->androidActivity = (string) $android_activity;
        $this->iosAppKey       = (string) $ios_key;
    }

    /**
     * @return string
     */
    public function getIosAppKey(): string
    {
        return $this->iosAppKey;
    }

    /**
     * @return string
     */
    public function getAndroidChannel(): string
    {
        return $this->androidChannel;
    }

    /**
     * @return string
     */
    public function getAndroidActivity(): string
    {
        return $this->androidActivity;
    }

    /**
     * @return string
     */
    public function getAndroidAppKey(): string
    {
        return $this->androidAppKey;
    }

    /**
     * @return string
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * @return string
     */
    public function getAccessSecret(): string
    {
        return $this->accessSecret;
    }

    /**
     * 默认配置
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