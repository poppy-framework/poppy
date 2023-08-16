<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\LianLuSmsProvider;
use Poppy\System\Exceptions\SettingKeyNotMatchException;
use Poppy\System\Exceptions\SettingValueOutOfRangeException;

/**
 * 发送短信
 */
class LianLuTest extends BaseSms
{

    public function setUp(): void
    {
        parent::setUp();
        // config
        config([
            'poppy.sms.sign'           => (string) data_get($this->conf, 'lianlu_sign'),
            'poppy.sms.lianlu.mch_id'  => data_get($this->conf, 'lianlu_mch_id'),
            'poppy.sms.lianlu.app_id'  => data_get($this->conf, 'lianlu_app_id'),
            'poppy.sms.lianlu.app_key' => data_get($this->conf, 'lianlu_app_key'),
        ]);
    }

    /**
     * 发送普通短信
     * @return void
     * @throws SettingKeyNotMatchException
     * @throws SettingValueOutOfRangeException
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
}