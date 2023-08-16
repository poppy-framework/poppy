<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Illuminate\Support\Str;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\AliyunSmsProvider;
use Poppy\Sms\Classes\PySmsHelper;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;

/**
 * 发送短信
 */
class AliyunTest extends BaseSms
{

    public function setUp(): void
    {
        parent::setUp();

        // config
        config([
            'poppy.sms.aliyun.access_key'    => data_get($this->conf, 'aliyun_access_key'),
            'poppy.sms.aliyun.access_secret' => data_get($this->conf, 'aliyun_access_secret'),
        ]);
    }


    public function testTransMobile(): void
    {
        $mobiles = '15555555551';
        // single
        $this->assertEquals('15555555551', PySmsHelper::transToAliyunMobile($mobiles));

        // single
        $mobiles = ['86-15555555552'];
        $this->assertEquals('8615555555552', PySmsHelper::transToAliyunMobile($mobiles));

        // multi
        $mobiles = ['15555555553', '16666666666'];
        $this->assertEquals('15555555553,16666666666', PySmsHelper::transToAliyunMobile($mobiles));

        // multi string
        $mobiles = '15555555554,16666666666';
        $this->assertEquals('15555555554,16666666666', PySmsHelper::transToAliyunMobile($mobiles));
    }

    /**
     * 测试短信发送
     * @return void
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     */
    public function testCaptcha(): void
    {
        $Sms = new Sms();
        $Sms->establish('aliyun:captcha', data_get($this->conf, 'aliyun_captcha_code'));


        $Provider = new AliyunSmsProvider();
        if ($Provider->send('captcha', $this->mobile, [
            'code' => 'Test_' . Str::random(4),
        ])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Provider->getError()->getMessage());
        }
    }
}