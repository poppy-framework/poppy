<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use Poppy\Framework\Helper\UtilHelper;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\Chuanglan\SmsApi;
use Poppy\Sms\Classes\Contracts\SmsContract;

class ChuanglanSmsProvider extends BaseSms implements SmsContract
{
    /**
     * @var SmsApi
     */
    private SmsApi $clApi;


    public function __construct()
    {
        parent::__construct();
        config([
            'poppy.sms.chuanglan.access_key'        => sys_setting('py-sms::sms.chuanglan_access_key'),
            'poppy.sms.chuanglan.access_secret'     => sys_setting('py-sms::sms.chuanglan_access_secret'),
            'poppy.sms.chuanglan.cty_access_key'    => sys_setting('py-sms::sms.chuanglan_cty_access_key'),
            'poppy.sms.chuanglan.cty_access_secret' => sys_setting('py-sms::sms.chuanglan_cty_access_secret'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function send(string $type, $mobile, array $params = [], $sign = ''): bool
    {
        $this->setScope(Sms::SCOPE_CHUANGLAN);
        if (!$this->checkSms($mobile, $type, $sign)) {
            return false;
        }
        $this->initConfig($mobile);

        $msg = sys_trans($this->sms['code'], $params);
        // 拼接签名
        $msg = '【' . str_replace(['【', '】', '[', ']'], '', $this->sign) . '】' . $msg;

        $result = UtilHelper::isChMobile($mobile)
            ? $this->clApi->sendSms($mobile, $msg)
            : $this->clApi->sendCtySMS($mobile, $msg);
        if (!is_null($result)) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] === '0') {
                return true;
            }

            return $this->setError($result);
        }

        return $this->setError($result);
    }

    /**
     * 初始化配置
     * @param string $mobiles
     */
    private function initConfig($mobiles): void
    {
        if (!UtilHelper::isChMobile($mobiles)) {
            $apiAccount  = config('poppy.sms.chuanglan.cty_access_key');
            $apiPassword = config('poppy.sms.chuanglan.cty_access_secret');
        }
        else {
            $apiAccount  = config('poppy.sms.chuanglan.access_key');
            $apiPassword = config('poppy.sms.chuanglan.access_secret');
        }
        $this->clApi = new SmsApi($apiAccount, $apiPassword);
    }
}
