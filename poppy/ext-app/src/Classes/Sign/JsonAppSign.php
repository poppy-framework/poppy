<?php

declare(strict_types = 1);

namespace Poppy\Extension\App\Classes\Sign;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\AppTrait;

/**
 * 默认的客户端验签
 */
class JsonAppSign
{
    use AppTrait;

    /**
     * @throws JsonException
     */
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

        $appid = (string) sys_get($input, 'appid');
        if (!$appid) {
            return $this->setError(new Resp(Resp::PARAM_ERROR, '请传入 Appid'));
        }

        $clients = config('services.kr-clients');
        $client  = $clients[$appid] ?? '';

        if (!$client) {
            return $this->setError('错误的 appid');
        }


        if (strlen($client['secret']) !== 32) {
            return $this->setError('错误的密钥');
        }

        // check sign
        if ($sign !== $this->calcSign($input, $client['secret'])) {
            sys_warning('poppy.app-json-sign_error', [], true);
            return $this->setError(new Resp(Resp::SIGN_ERROR, '签名错误'));
        }
        return true;
    }

    /**
     * 计算验签
     * @param array  $params 参数
     * @param string $appid 应用 ID
     * @param string $secret 密钥
     * @return array
     * @throws JsonException
     */
    public function sign(array $params, string $appid, string $secret): array
    {
        $params         = array_merge($params, [
            'appid'     => $appid,
            'timestamp' => Carbon::now()->timestamp,
        ]);
        $sign           = $this->calcSign($params, $secret);
        $params['sign'] = $sign;

        return $params;
    }

    /**
     * 对数据进行签名, 并返回 md5 的数据
     * @param array  $params 参数
     * @param string $secret 密钥
     * @return string
     * @throws JsonException
     */
    protected function calcSign(array $params, string $secret): string
    {
        $params = $this->except($params);
        ksort($params);
        $kvStr = json_encode($params, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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