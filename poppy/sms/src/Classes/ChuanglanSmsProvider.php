<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use Poppy\Framework\Helper\UtilHelper;
use Poppy\Sms\Classes\Chuanglan\SmsApi;
use Poppy\Sms\Classes\Contracts\SmsContract;

class ChuanglanSmsProvider extends BaseSms implements SmsContract
{
    /**
     * @var SmsApi
     */
    private SmsApi $clApi;

    /**
     * @inheritDoc
     */
    public function send(string $type, $mobile, array $params = [], $sign = ''): bool
    {
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
