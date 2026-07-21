<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use Darabonba\OpenApi\Models\Config;
use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\Contracts\SmsContract;
use Poppy\Sms\Exceptions\SmsException;

class AliyunSmsProvider extends BaseSms implements SmsContract
{
    /**
     * {@inheritDoc}
     */
    public function send(string $type, $mobile, array $params = [], $sign = ''): bool
    {
        $this->setScope(Sms::SCOPE_ALIYUN);
        if (!$this->checkSms($mobile, $type, $sign)) {
            return false;
        }

        // 支持数组/字串/多字串
        $mobile = PySmsHelper::transToAliyunMobile($mobile);

        try {
            $client = $this->initClient();

            /**
             * @url https://help.aliyun.com/document_detail/101414.htm
             */
            $request               = new SendSmsRequest();
            $request->phoneNumbers = $mobile;
            $request->signName     = $this->sign;
            $request->templateCode = $this->sms['code'];
            if ($params) {
                $request->templateParam = json_encode($params, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }
            $resp = $client->sendSms($request);

            /*
             * 返回的信息如下所示, 如果失败 Message 中是错误的信息
             * {
             *    "RequestId":"04B69136-3DF5-4418-9A8C-5A9278608259",
             *    "Message":"OK",
             *    "BizId":"340723709666881413^0",
             *    "Code":"OK"
             * }
             */

            if ('OK' === $resp->body->code) {
                return true;
            }

            return $this->setError('Aliyun:' . $resp->body->message);
        }
        catch (SmsException $e) {
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 初始化
     *
     * @throws SmsException
     */
    private function initClient(): Dysmsapi
    {
        $accessKeyId     = (string) sys_setting('py-sms::sms.aliyun_access_key');
        $accessKeySecret = (string) sys_setting('py-sms::sms.aliyun_access_secret');
        if (!class_exists(Dysmsapi::class)) {
            throw new SmsException('你需要手动安装 `alibabacloud/dysmsapi-20170525` 组件');
        }

        $config           = new Config([
            'accessKeyId'     => $accessKeyId,
            'accessKeySecret' => $accessKeySecret,
        ]);
        $config->endpoint = 'dysmsapi.aliyuncs.com';

        return new Dysmsapi($config);
    }
}
