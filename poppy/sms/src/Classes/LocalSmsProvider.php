<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use Poppy\Sms\Action\Sms;
use Poppy\Sms\Classes\Contracts\SmsContract;

/**
 * 本地发送短信, 记录在日志中
 */
class LocalSmsProvider extends BaseSms implements SmsContract
{

    /**
     * @inheritDoc
     */
    public function send(string $type, $mobile, array $params = [], $sign = ''): bool
    {
        $this->setScope(Sms::SCOPE_LOCAL);
        if (!$this->checkSms($mobile, $type, $sign)) {
            return false;
        }
        // 未选择则使用日志, 线上不记录日志
        $sign    = $this->sign;
        $trans   = sys_trans($this->sms['code'], $params);
        $content = ($sign ? "[{$sign}]" : '') . $trans;
        Log::info(sys_gen_mk(self::class, $content));

        return true;
    }
}
