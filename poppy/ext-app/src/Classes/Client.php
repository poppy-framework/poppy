<?php

declare(strict_types = 1);

namespace Poppy\Extension\App\Classes;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Poppy\Extension\App\Classes\Sign\DefaultAppSign;

class Client
{

    private const ERR_JSON = 901;

    /**
     * 应用 ID
     * @var int
     */
    private int $appid;

    /**
     * 密钥
     * @var string
     */
    private string $secret = '';


    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    public function __construct()
    {
        $this->client = new GuzzleClient();
    }

    /**
     * 发送应用 GET 请求
     * @param string $url
     * @param array  $query
     * @return array|mixed
     */
    public function get(string $url, array $query = [])
    {
        $query = DefaultAppSign::sign($query, $this->appid, $this->secret);
        try {
            $resp    = $this->client->get($url, [
                'query' => $query,
            ]);
            $content = $resp->getBody()->getContents();
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            return [
                'status'  => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        } catch (JsonException $e) {
            return [
                'status'  => self::ERR_JSON,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 发送应用 POST 请求
     * @param string $url
     * @param array  $form_params
     * @return array|mixed
     */
    public function post(string $url, array $form_params = [])
    {
        $form_params = DefaultAppSign::sign($form_params, $this->appid, $this->secret);

        try {
            $resp    = $this->client->post($url, [
                'form_params' => $form_params,
            ]);
            $content = $resp->getBody()->getContents();
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            return [
                'status'  => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        } catch (JsonException $e) {
            return [
                'status'  => self::ERR_JSON,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param int $appid
     * @return Client
     */
    public function setAppid(int $appid): Client
    {
        $this->appid = $appid;
        return $this;
    }

    /**
     * @param string $secret
     * @return Client
     */
    public function setSecret(string $secret): Client
    {
        $this->secret = $secret;
        return $this;
    }
}