<?php

declare(strict_types = 1);

namespace Poppy\Sms\Tests;

use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Sms\Classes\Contracts\SmsContract;

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
     */
    public function testSendSms()
    {
        /** @var SmsContract|AppTrait $Sms */
        $Sms = app('poppy.sms');
        if ($Sms->send('cash_over', $this->mobile, ['type' => 'normal'])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }

    /**
     * 发送模板短信
     */
    public function testTemplateSms(): void
    {
        /** @var SmsContract|AppTrait $Sms */
        $Sms = app('poppy.sms');
        if ($Sms->send('goods_fail', $this->mobile, ['type' => 'template', 'params' => ['11', '失败原因']])) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Sms->getError()->getMessage());
        }
    }
}