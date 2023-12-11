<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Tests\Testing;

class TestingAliyunOss
{
    /**
     * 返回从系统读取的配置文件
     * @return array
     */
    public static function config(): array
    {
        return [
            'access_key'      => env('PY_ALIYUN_OSS_ACCESS_KEY'),
            'access_secret'   => env('PY_ALIYUN_OSS_ACCESS_SECRET'),
            'endpoint'        => env('PY_ALIYUN_OSS_ENDPOINT'),
            'bucket'          => env('PY_ALIYUN_OSS_BUCKET'),
            'url_prefix'      => env('PY_ALIYUN_OSS_URL_PREFIX'),
            'role_arn'        => env('PY_ALIYUN_OSS_ROLE_ARN'),
            'temp_app_key'    => env('PY_ALIYUN_OSS_TEMP_APP_KEY'),
            'temp_app_secret' => env('PY_ALIYUN_OSS_TEMP_APP_SECRET'),
            'watermark'       => env('PY_ALIYUN_OSS_WATERMARK'),
        ];
    }
}
