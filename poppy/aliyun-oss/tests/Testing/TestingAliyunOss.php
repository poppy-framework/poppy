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
            'poppy.aliyun-oss.access_key'      => env('PY_ALIYUN_OSS_ACCESS_KEY'),
            'poppy.aliyun-oss.access_secret'   => env('PY_ALIYUN_OSS_ACCESS_SECRET'),
            'poppy.aliyun-oss.endpoint'        => env('PY_ALIYUN_OSS_ENDPOINT'),
            'poppy.aliyun-oss.bucket'          => env('PY_ALIYUN_OSS_BUCKET'),
            'poppy.aliyun-oss.url'             => env('PY_ALIYUN_OSS_URL_PREFIX'),
            'poppy.aliyun-oss.role_arn'        => env('PY_ALIYUN_OSS_ROLE_ARN'),
            'poppy.aliyun-oss.temp_app_key'    => env('PY_ALIYUN_OSS_TEMP_APP_KEY'),
            'poppy.aliyun-oss.temp_app_secret' => env('PY_ALIYUN_OSS_TEMP_APP_SECRET'),
            'poppy.aliyun-oss.watermark'       => env('PY_ALIYUN_OSS_WATERMARK'),
        ];
    }
}
