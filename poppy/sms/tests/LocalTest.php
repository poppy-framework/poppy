<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Illuminate\Support\Str;
use Poppy\Sms\Classes\LocalSmsProvider;

/**
 * 发送短信
 */
class LocalTest extends BaseSms
{
    /**
     * 测试短信发送
     */
    public function testCaptcha(): void
    {
        $Sms = new LocalSmsProvider();
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
