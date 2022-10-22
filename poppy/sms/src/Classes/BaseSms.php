<?php

namespace Poppy\Sms\Classes;

use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Sms\Action\Sms;

abstract class BaseSms
{
    use AppTrait;

    /**
     * @var array 短信
     */
    protected array $sms;

    /**
     * 短信签名
     * @var string
     */
    protected string $sign;

    /**
     * 检查短信是否为空
     * @param string|array $mobile 手机号
     * @param string       $type   类型
     * @param string       $sign   签名
     * @return bool
     */
    public function checkSms($mobile, string $type, string $sign): bool
    {
        if (!$mobile) {
            return $this->setError('手机号缺失, 不进行发送!');
        }

        if (!$type) {
            return $this->setError('短信类型不存在, 不进行发送');
        }
        $this->sms = Sms::smsTpl($type);

        if (!$this->sms) {
            return $this->setError('请设置短信模板');
        }

        $configSign = (string) config('poppy.sms.sign', '');
        $this->sign = $configSign;
        if ($sign) {
            $this->sign = $sign;
        }

        if (!$this->sign) {
            return $this->setError('尚未设置签名, 无法发送');
        }

        return true;
    }
}
