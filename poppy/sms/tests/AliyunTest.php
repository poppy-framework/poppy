<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Illuminate\Support\Str;
use Poppy\Sms\Action\Sms;

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
            'poppy.sms.send_type'            => Sms::SCOPE_ALIYUN,
            'poppy.sms.aliyun.access_key'    => data_get($this->conf, 'aliyun_access_key'),
            'poppy.sms.aliyun.access_secret' => data_get($this->conf, 'aliyun_access_secret'),
        ]);
    }


    public function testMobile()
    {
        $mobiles      = '15555555551';
        $carryMobiles = function ($mobiles) {
            return array_reduce((array) $mobiles, function ($carry, $mobile) {
                $mobile = str_replace('-', '', $mobile);
                return $carry ? $carry . ',' . $mobile : $mobile;
            }, '');
        };
        // single
        $this->assertEquals('15555555551', $carryMobiles($mobiles));

        // single
        $mobiles = ['86-15555555552'];
        $this->assertEquals('8615555555552', $carryMobiles($mobiles));

        // multi
        $mobiles = ['15555555553', '16666666666'];
        $this->assertEquals('15555555553,16666666666', $carryMobiles($mobiles));

        // multi string
        $mobiles = '15555555554,16666666666';
        $this->assertEquals('15555555554,16666666666', $carryMobiles($mobiles));
    }

    /**
     * 测试短信发送
     */
    public function testCaptcha(): void
    {
        $Sms = app('poppy.sms');
        if ($Sms->send('captcha', $this->mobile, [
            'code' => 'Test_' . Str::random(4),
        ])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }

    /**
     * 测试短信发送
     */
    public function testHandle(): void
    {
        $Sms = app('poppy.sms');
        if ($Sms->send('handle', $this->mobile)) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }
}