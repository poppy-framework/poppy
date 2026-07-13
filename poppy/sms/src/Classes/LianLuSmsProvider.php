<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use JsonException;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\Contracts\SmsContract;
use Poppy\Sms\Classes\LianLu\SmsApi;

class LianLuSmsProvider extends BaseSms implements SmsContract
{
    /**
     * @var SmsApi
     */
    private SmsApi $llApi;

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function send(string $type, $mobile, array $params = [], $sign = ''): bool
    {
        $this->setScope(Sms::SCOPE_LIANLU);
        if (!$this->checkSms($mobile, $type, $sign)) {
            return false;
        }

        $this->initConfig($mobile);

        $templateId     = $this->sms['code'] ?? '';
        $templateParams = array_values($params);
        if (!$templateId) {
            return $this->setError('未设置模版ID');
        }
        if (!UtilHelper::isChMobile($mobile)) {
            return $this->setError('暂不支持国际短信发送');
        }
        $result = $this->llApi->sendTemplateSMS($mobile, $templateId, $templateParams);
        if (!is_null($result)) {
            $output = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
            if (isset($output['status']) && $output['status'] === '00') {
                return true;
            }

            return $this->setError($result);
        }

        return $this->setError($result);
    }

    /**
     * 初始化配置
     *
     * @param string $mobile
     */
    private function initConfig(string $mobile): void
    {
        if (UtilHelper::isChMobile($mobile)) {
            $mchId  = (string) sys_setting('py-sms::sms.lianlu_mch_id');
            $appId  = (string) sys_setting('py-sms::sms.lianlu_app_id');
            $appKey = (string) sys_setting('py-sms::sms.lianlu_app_key');
        }
        else {
            $mchId  = (string) sys_setting('py-sms::sms.lianlu_cty_mch_id');
            $appId  = (string) sys_setting('py-sms::sms.lianlu_cty_app_id');
            $appKey = (string) sys_setting('py-sms::sms.lianlu_cty_app_key');
        }
        $this->llApi = new SmsApi($mchId, $appId, $appKey, $this->sign);
    }
}
