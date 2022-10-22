<?php

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
            'access_key'      => sys_setting('py-aliyun-oss::oss.access_key'),
            'access_secret'   => sys_setting('py-aliyun-oss::oss.access_secret'),
            'endpoint'        => sys_setting('py-aliyun-oss::oss.endpoint'),
            'bucket'          => sys_setting('py-aliyun-oss::oss.bucket'),
            'url_prefix'      => sys_setting('py-aliyun-oss::oss.url_prefix'),
            'role_arn'        => sys_setting('py-aliyun-oss::oss.role_arn'),
            'temp_app_key'    => sys_setting('py-aliyun-oss::oss.temp_app_key'),
            'temp_app_secret' => sys_setting('py-aliyun-oss::oss.temp_app_secret'),
            'watermark'       => sys_setting('py-aliyun-oss::oss.watermark'),
        ];
    }
}
