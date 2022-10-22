<?php

namespace Poppy\AliyunPush\Tests\Push;


use Notification;
use Poppy\AliyunPush\Classes\BindTag;
use Poppy\AliyunPush\Classes\Config\Config;
use Poppy\AliyunPush\Classes\Sender\PushMessage;
use Poppy\AliyunPush\Tests\Sample\AndroidAllNoticeNotification;
use Poppy\AliyunPush\Tests\Sample\AndroidMessageNotification;
use Poppy\AliyunPush\Tests\Sample\AndroidNoticeNotification;
use Poppy\AliyunPush\Tests\Sample\IosMessageNotification;
use Poppy\AliyunPush\Tests\Sample\IosNoticeNotification;
use Poppy\AliyunPush\Tests\Sample\IosTagBoyPushNotification;
use Poppy\AliyunPush\Tests\Sample\IosTagGirlPushNotification;
use Poppy\AliyunPush\Tests\Sample\IosTagIosPushNotification;
use Poppy\System\Tests\Base\SystemTestCase;
use Throwable;

/**
 * 推送测试
 */
class PushTest extends SystemTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        // config
        app('config')->set([
            'poppy.aliyun-push.access_key'       => sys_setting('py-aliyun-push::push.access_key'),
            'poppy.aliyun-push.access_secret'    => sys_setting('py-aliyun-push::push.access_secret'),
            'poppy.aliyun-push.android_app_key'  => sys_setting('py-aliyun-push::push.android_app_key'),
            'poppy.aliyun-push.ios_app_key'      => sys_setting('py-aliyun-push::push.ios_app_key'),
            'poppy.aliyun-push.android_activity' => sys_setting('py-aliyun-push::push.android_activity'),
            'poppy.aliyun-push.android_channel'  => sys_setting('py-aliyun-push::push.android_channel'),
            'poppy.aliyun-push.ios_is_open'      => sys_setting('py-aliyun-push::push.ios_is_open'),
            'poppy.aliyun-push.android_is_open'  => sys_setting('py-aliyun-push::push.android_is_open'),
            'poppy.aliyun-push.registration_ids' => [
                'ios'     => [
                    'd733ae6c57754f22a4de519e0eafe816',
                    'ddc547251d204e98ab1c664dc44b50ba',
                    'b59f5b4cfc764599843f277e1a092adb',
                ],
                'android' => [
                    'daa0b0b2887f4f1fb4c1084d06a729f6',
                    'c7e36ac34833a66e7722f8cfde5b9256',
                    '7408a80f8dd04f5c809ec34f544e9019',
                    '7d04dd3d071641d4a2bd5d5b7dcecb21',
                ],
            ],
        ]);
    }

    public function testSendAndroidNotice()
    {
        try {
            Notification::send(null, new AndroidNoticeNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendAndroidAll()
    {
        try {
            Notification::send(null, new AndroidAllNoticeNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendAndroidMessage()
    {
        try {
            Notification::send(null, new AndroidMessageNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendIosNotice()
    {
        try {
            Notification::send(null, new IosNoticeNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendIosMessage()
    {
        try {
            Notification::send(null, new IosMessageNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * 绑定倩倩的设备号设定标签为 girl
     */
    public function testBindGirl()
    {
        try {
            $Bind = new BindTag(Config::default());
            $qqId = 'a6e8a2f36e2d4da9a22762362a987476';
            $dyId = 'd733ae6c57754f22a4de519e0eafe816';
            $zxId = 'b59f5b4cfc764599843f277e1a092adb';
            $Bind->bindDevice(PushMessage::DEVICE_TYPE_IOS, 'girl', $qqId); // 倩倩
            $Bind->bindDevice(PushMessage::DEVICE_TYPE_IOS, 'boy', $zxId);  // 张新
            $Bind->bindDevice(PushMessage::DEVICE_TYPE_IOS, 'boy', $dyId);  // 赵殿有
            $Bind->bindDevice(PushMessage::DEVICE_TYPE_IOS, 'ios', $qqId);
            $Bind->bindDevice(PushMessage::DEVICE_TYPE_IOS, 'ios', $zxId);
            $Bind->bindDevice(PushMessage::DEVICE_TYPE_IOS, 'ios', $dyId);
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendIosTagGirl()
    {
        try {
            Notification::send(null, new IosTagGirlPushNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendIosTagIosPush()
    {
        try {
            Notification::send(null, new IosTagIosPushNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSendIosTagBoyPush()
    {
        try {
            Notification::send(null, new IosTagBoyPushNotification());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }
}