<?php

declare(strict_types = 1);

namespace Poppy\App\Classes\Sign;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Log;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\ArrayHelper;

/**
 * 默认的应用验签
 */
class DefaultAppSign
{
    use AppTrait;

    public function check(array $input): bool
    {
        // 加密 debug, 不验证签名
        $pySecret = (string) config('poppy.system.secret');
        if ($pySecret && (string) sys_get($input, '_py_secret') === $pySecret) {
            return true;
        }

        // check token
        $timestamp = (string) sys_get($input, 'timestamp');
        if (!$timestamp) {
            return $this->setError(new Resp(Resp::PARAM_ERROR, '未传递时间戳'));
        }

        // check token
        $sign = (string) sys_get($input, 'sign');
        if (!$sign) {
            return $this->setError(new Resp(Resp::PARAM_ERROR, '未进行签名'));
        }

        $appid = (int) sys_get($input, 'appid');
        if (!$appid) {
            return $this->setError(new Resp(Resp::PARAM_ERROR, '请传入 Appid'));
        }

        $item = SysApp::item($appid);
        if (!$item) {
            return $this->setError('错误的 appid');
        }

        if (!$item['is_enable']) {
            return $this->setError('此应用已禁用');
        }

        if (strlen($item['secret']) !== 32) {
            return $this->setError('错误的密钥');
        }

        // check sign
        if ($sign !== $this->calcSign($input, $item['secret'])) {
            Log::error('sign-error', [
                'params' => $input,
            ]);
            return $this->setError(new Resp(Resp::SIGN_ERROR, '签名错误'));
        }
        return true;
    }

    /**
     * 计算验签
     * @param array  $params 参数
     * @param int    $appid  应用 ID
     * @param string $secret 密钥
     * @return array
     */
    public function sign(array $params, int $appid, string $secret): array
    {
        sys_info('origin-params', $params);
        $params = array_merge($params, [
            'appid'     => $appid,
            'timestamp' => Carbon::now()->timestamp,
        ]);
        sys_info('append-params', $params);
        sys_info('app-secret', $secret);
        $sign           = $this->calcSign($params, $secret);
        $params['sign'] = $sign;

        sys_info('fully-params', $params);
        return $params;
    }

    /**
     * 对数据进行签名, 并返回 md5 的数据
     * @param array  $params 参数
     * @param string $secret 密钥
     * @return string
     */
    protected function calcSign(array $params, string $secret): string
    {
        $params = $this->except($params);
        sys_info('cleared-params', $params);
        ksort($params);
        sys_info('sorted-params', $params);
        $kvStr = ArrayHelper::toKvStr($params);
        sys_info('kv-params', $kvStr);
        return md5(md5($kvStr) . $secret);
    }

    /**
     * @param $params
     * @return array
     */
    protected function except($params): array
    {
        $excepts = [];
        foreach ($params as $key => $param) {
            if (!Str::startsWith($key, '_')) {
                if (is_array($param)) {
                    $excepts[$key] = $param;
                }
                else {
                    $excepts[$key] = trim((string) $param);
                }
            }
        }
        return Arr::except($excepts, [
            'sign', 'image', 'file', 'appid',
        ]);
    }
}