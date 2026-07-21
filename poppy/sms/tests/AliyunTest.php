<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use JsonException;
use Poppy\Framework\Helper\StrHelper;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\AliyunSmsProvider;
use Poppy\Sms\Classes\PySmsHelper;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Throwable;

/**
 * 发送短信
 */
class AliyunTest extends BaseSms
{
    private string $previousAliyunAccessKey = '';

    private string $previousAliyunAccessSecret = '';

    private string $previousSignName = '';

    /**
     * @throws SettingValueOutOfRangeException
     * @throws SettingKeyNotMatchException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->previousAliyunAccessKey    = (string) sys_setting('py-sms::sms.aliyun_access_key');
        $this->previousAliyunAccessSecret = (string) sys_setting('py-sms::sms.aliyun_access_secret');
        $this->previousSignName           = (string) sys_setting('py-sms::sms.sign');

        app('poppy.system.setting')->set([
            'py-sms::sms.aliyun_access_key'    => data_get($this->conf, 'aliyun_access_key'),
            'py-sms::sms.aliyun_access_secret' => data_get($this->conf, 'aliyun_access_secret'),
            'py-sms::sms.sign'                 => data_get($this->conf, 'aliyun_sign'),
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
     *
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     * @throws JsonException
     */
    public function testCaptcha(): void
    {
        $Sms = new Sms();
        $Sms->establish('aliyun:captcha', data_get($this->conf, 'aliyun_captcha_code'));

        $Provider = new AliyunSmsProvider();
        if ($Provider->send('captcha', $this->mobile, [
            'code' => StrHelper::randomNumber(111111, 999999),
        ])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Provider->getError()->getMessage());
        }
    }

    /**
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     * @throws Throwable
     */
    public function tearDown(): void
    {
        app('poppy.system.setting')->set([
            'py-sms::sms.aliyun_access_key'    => $this->previousAliyunAccessKey,
            'py-sms::sms.aliyun_access_secret' => $this->previousAliyunAccessSecret,
            'py-sms::sms.sign'                 => $this->previousSignName,
        ]);
        parent::tearDown();
    }
}
