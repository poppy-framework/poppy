<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Illuminate\Support\Str;

/**
 * 发送短信
 */
class ChuanglanTest extends BaseSms
{

    public function setUp(): void
    {
        parent::setUp();
        // config
        config([
            'poppy.sms.sign'                        => (string) data_get($this->conf, 'chuanglan_sign'),
            'poppy.sms.chuanglan.access_key'        => data_get($this->conf, 'chuanglan_access_key'),
            'poppy.sms.chuanglan.access_secret'     => data_get($this->conf, 'chuanglan_access_secret'),
            'poppy.sms.chuanglan.cty_access_key'    => data_get($this->conf, 'chuanglan_cty_access_key'),
            'poppy.sms.chuanglan.cty_access_secret' => data_get($this->conf, 'chuanglan_cty_access_secret'),
        ]);
    }

    /**
     * 测试短信发送
     */
    public function testCaptcha(): void
    {
        $Sms = app('poppy.sms');
        if ($Sms->send('captcha', $this->mobile, [
            'code' => 'Test_' . Str::random(4),
        ], config('poppy.sms.sign'))) {
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
        if ($Sms->send('handle', $this->mobile, [], config('poppy.sms.sign'))) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }
}