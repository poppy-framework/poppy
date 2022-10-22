<?php

namespace Poppy\Sms\Classes\Contracts;

/**
 * 短信实现
 */
interface SmsContract
{
    /**
     * 发送短信
     * @param string       $type   发送类型
     * @param string|array $mobile 接收手机号, 支持数组
     * @param array        $params 参数
     * @param string       $sign   签名, 不填写使用默认签名
     * @return mixed
     */
    public function send(string $type, $mobile, array $params = [], string $sign = ''): bool;
}