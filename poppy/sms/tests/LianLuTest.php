<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use JsonException;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\LianLuSmsProvider;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;
use Throwable;

/**
 * 发送短信
 */
class LianLuTest extends BaseSms
{
    private string $previousSign = '';
    private string $previousMchId = '';
    private string $previousAppId = '';
    private string $previousAppKey = '';

    /**
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->previousSign   = (string) sys_setting('py-sms::sms.sign');
        $this->previousMchId  = (string) sys_setting('py-sms::sms.lianlu_mch_id');
        $this->previousAppId  = (string) sys_setting('py-sms::sms.lianlu_app_id');
        $this->previousAppKey = (string) sys_setting('py-sms::sms.lianlu_app_key');

        app('poppy.system.setting')->set([
            'py-sms::sms.sign'           => (string) data_get($this->conf, 'lianlu_sign'),
            'py-sms::sms.lianlu_mch_id'  => data_get($this->conf, 'lianlu_mch_id'),
            'py-sms::sms.lianlu_app_id'  => data_get($this->conf, 'lianlu_app_id'),
            'py-sms::sms.lianlu_app_key' => data_get($this->conf, 'lianlu_app_key'),
        ]);
    }

    /**
     * 发送普通短信
     * @return void
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     * @throws JsonException
     */
    public function testSendSms(): void
    {

        $Sms = new Sms();
        $Sms->establish('lianlu:cash_over', data_get($this->conf, 'lianlu_cash_over_code'));

        $Sms = new LianLuSmsProvider();
        if ($Sms->send('cash_over', $this->mobile, ['type' => 'normal'])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }

    /**
     * 发送带有参数的模板短信
     * @return void
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
     * @throws JsonException
     */
    public function testTemplateSms(): void
    {
        $Sms = new Sms();
        $Sms->establish('lianlu:goods_fail', data_get($this->conf, 'lianlu_goods_fail_code'));

        $Sms = new LianLuSmsProvider();
        if ($Sms->send('goods_fail', $this->mobile, ['高级原神账号', '商品价值'])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }

    /**
     * @throws SettingValueOutOfRangeException
     * @throws Throwable
     * @throws SettingKeyNotMatchException
     */
    public function tearDown(): void
    {
        app('poppy.system.setting')->set([
            'py-sms::sms.sign'           => $this->previousSign,
            'py-sms::sms.lianlu_mch_id'  => $this->previousMchId,
            'py-sms::sms.lianlu_app_id'  => $this->previousAppId,
            'py-sms::sms.lianlu_app_key' => $this->previousAppKey,
        ]);
        parent::tearDown();
    }
}