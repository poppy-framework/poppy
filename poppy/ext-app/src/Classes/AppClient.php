<?php

declare(strict_types = 1);

namespace Poppy\Extension\App\Classes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use JsonException;
use Poppy\Extension\App\Classes\Sign\DefaultAppSign;

class AppClient
{
    private const ERR_JSON = 901;

    /**
     * 应用 ID
     *
     * @var int|string
     */
    private $appid;

    /**
     * 密钥
     */
    private string $secret = '';

    private GuzzleClient $client;

    private string $baseUrl;

    private bool $log = false;

    public function __construct($base_url = '')
    {
        $this->client  = new GuzzleClient();
        $this->baseUrl = $base_url;
    }

    /**
     * 发送应用 GET 请求
     *
     * @return array|mixed
     */
    public function get(string $url, array $query = [])
    {
        $query = DefaultAppSign::sign($query, $this->appid, $this->secret);
        try {
            $resp    = $this->client->get($this->url($url), [
                'query' => $query,
            ]);
            $content = $resp->getBody()->getContents();
            $data    = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $this->log($url, __METHOD__, $query, $data);

            return $data;
        }
        catch (GuzzleException|JsonException $e) {
            return $this->handleException($e, $url, __METHOD__, $query);
        }
    }

    public function file(string $url, $params, $filepath)
    {
        $form_params = DefaultAppSign::sign($params, $this->appid, $this->secret);
        $multipart   = [];
        foreach ($form_params as $key => $param) {
            $multipart[] = [
                'name'     => $key,
                'contents' => $param,
            ];
        }

        $multipart[] = [
            'name'     => 'file',
            'contents' => Utils::tryFopen($filepath, 'r'),
            'filename' => basename($filepath),
        ];

        try {
            $resp    = $this->client->post($this->url($url), [
                'multipart' => $multipart,
            ]);
            $content = $resp->getBody()->getContents();
            $data    = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $this->log($url, __METHOD__, $form_params, $data);

            return $data;
        }
        catch (GuzzleException|JsonException $e) {
            return $this->handleException($e, $url, __METHOD__, $params);
        }
    }

    /**
     * 发送应用 POST 请求
     *
     * @return array|mixed
     */
    public function post(string $url, array $form_params = [])
    {
        $form_params = DefaultAppSign::sign($form_params, $this->appid, $this->secret);

        try {
            $resp    = $this->client->post($this->url($url), [
                'form_params' => $form_params,
            ]);
            $content = $resp->getBody()->getContents();
            $data    = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $this->log($url, __METHOD__, $form_params, $data);

            return $data;
        }
        catch (GuzzleException|JsonException $e) {
            return $this->handleException($e, $url, __METHOD__, $form_params);
        }
    }

    /**
     * 发送应用 JSON 请求
     *
     * @return array|mixed
     */
    public function json(string $url, array $params = [])
    {
        $params = DefaultAppSign::sign($params, $this->appid, $this->secret);

        try {
            $resp    = $this->client->post($this->url($url), [
                'json' => $params,
            ]);
            $content = $resp->getBody()->getContents();
            $data    = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $this->log($url, __METHOD__, $params, $data);

            return $data;
        }
        catch (GuzzleException|JsonException $e) {
            return $this->handleException($e, $url, __METHOD__, $params);
        }
    }

    /**
     * @param int|string $appid
     */
    public function setAppid($appid): AppClient
    {
        $this->appid = $appid;

        return $this;
    }

    public function setSecret(string $secret): AppClient
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * 启用日志
     *
     * @return $this
     */
    public function enableLog(): self
    {
        $this->log = true;

        return $this;
    }

    private function log($url, $method, $params, $resp): void
    {
        if (!$this->log) {
            return;
        }
        sys_info('poppy.ext-app-log', [
            'url'    => $url,
            'method' => $method,
            'params' => $params,
            'resp'   => $resp,
        ]);
    }

    /**
     * @param GuzzleException|JsonException $e
     */
    private function handleException($e, string $url, string $method, array $params): array
    {
        $data = [
            'status'  => $e instanceof JsonException ? self::ERR_JSON : $e->getCode(),
            'message' => $e->getMessage(),
        ];
        sys_error('poppy.ext-app-log', [
            'url'       => $url,
            'method'    => $method,
            'params'    => $params,
            'exception' => $data,
        ]);

        return $data;
    }

    private function url(string $url): string
    {
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
