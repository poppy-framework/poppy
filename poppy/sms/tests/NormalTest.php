<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Illuminate\Support\Str;
use Poppy\Sms\Classes\Contracts\SmsContract;

/**
 * 发送短信
 */
class NormalTest extends BaseSms
{
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

    public function testContract(): void
    {
        $Sms = app(SmsContract::class);
        if ($Sms->send('captcha', $this->mobile, [
            'code' => 'Test_' . Str::random(4),
        ])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }
}
