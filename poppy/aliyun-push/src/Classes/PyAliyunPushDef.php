<?php

namespace Poppy\AliyunPush\Classes;

class PyAliyunPushDef
{
    /**
     * 完善配置
     * @return void
     */
    public static function fillConfig(): void
    {
        config([
            'poppy.aliyun-push.access_key'       => sys_setting('py-aliyun-push::push.access_key'),
            'poppy.aliyun-push.access_secret'    => sys_setting('py-aliyun-push::push.access_secret'),
            'poppy.aliyun-push.ios_is_open'      => sys_setting('py-aliyun-push::push.ios_is_open'),
            'poppy.aliyun-push.ios_app_key'      => sys_setting('py-aliyun-push::push.ios_app_key'),
            'poppy.aliyun-push.android_is_open'  => sys_setting('py-aliyun-push::push.android_is_open'),
            'poppy.aliyun-push.android_app_key'  => sys_setting('py-aliyun-push::push.android_app_key'),
            'poppy.aliyun-push.android_channel'  => sys_setting('py-aliyun-push::push.android_channel'),
            'poppy.aliyun-push.android_activity' => sys_setting('py-aliyun-push::push.android_activity'),
        ]);
    }
}