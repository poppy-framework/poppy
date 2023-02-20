<?php

declare(strict_types = 1);

namespace Poppy\System\Action;

use Illuminate\Support\Str;
use Poppy\Extension\App\Classes\AppClient;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Exceptions\LoadConfigurationException;

/**
 * 对接 Console 中台
 */
class Console
{
    use AppTrait;

    /**
     * 应用控制台客户端
     * @var AppClient
     */
    private AppClient $client;

    /**
     * 控制台 URL
     * @var string
     */
    private string $host;

    /**
     * 汇报之后的地址
     * @var string
     */
    private string $cpUrl;

    /**
     * 应用 ID
     * @var string
     */
    private string $appid;

    /**
     * @throws LoadConfigurationException
     */
    public function __construct()
    {
        $this->appid = (string) env('CP_APPID');
        $secret      = env('CP_SECRET');
        $this->host  = env('CP_URL');
        if (!$this->appid || !$secret) {
            throw new LoadConfigurationException('Cp 控制台密钥未设置');
        }
        $this->client = (new AppClient())->setAppid($this->appid)->setSecret($secret);
    }

    /**
     * 抓取 clockwork
     * @param $id
     * @return bool
     */
    public function clockworkCapture($id): bool
    {
        if (!$this->checkAppId()) {
            return false;
        }
        $cwUrl = $this->host . '/api_v1/op/app/clockwork/capture';
        $file  = storage_path('clockwork/' . $id . '.json');
        if (!app('files')->exists($file)) {
            return $this->setError('文件不存在');
        }

        $resp = $this->client->file($cwUrl, [], $file);

        $status  = data_get($resp, 'status');
        $message = data_get($resp, 'message');
        if ($status === 0) {
            app('files')->delete($file);

            // replace index file
            $file = storage_path('clockwork/index');
            $re   = '/' . $id . '.*\n/m';
            file_put_contents($file, preg_replace($re, '', file_get_contents($file)));

            $this->cpUrl = $this->host . '/clockwork';
            return true;
        }
        return $this->setError($message);
    }


    /**
     * 生成密钥并汇报
     * @return bool
     */
    public function generateSecret(): bool
    {
        $name   = (string) env('APP_NAME');
        $env    = (string) env('APP_ENV');
        $url    = $this->host . '/api_v1/op/app/project/save-secret';
        $secret = md5(microtime(true) . Str::random());
        app('poppy.system.setting')->set('py-system::_.secret', $secret);

        if (!$this->checkAppId()) {
            return false;
        }

        $resp = $this->client->post($url, [
            'name'  => $name,
            'env'   => $env,
            'value' => $secret,
        ]);

        $status  = data_get($resp, 'status');
        $message = data_get($resp, 'message');
        if ($status === 0) {
            return true;
        }
        return $this->setError('已生成, 上报失败:' . $message);
    }

    /**
     * 当前的密钥
     * @return mixed
     */
    public function secret()
    {
        return sys_setting('py-system::_.secret');
    }

    /**
     * @return string
     */
    public function getCpUrl(): string
    {
        return $this->cpUrl;
    }

    private function checkAppId(): bool
    {
        if (!$this->appid) {
            return $this->setError('当前未设置中台应用, 不进行上报');
        }
        return true;
    }
}