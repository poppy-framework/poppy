<?php
declare(strict_types = 1);

namespace Poppy\Extension\Webhook\DingTalk;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Poppy\Extension\Webhook\Contracts\DingTalkMessage;

/**
 * DINGDING webhook
 * Class Dingding
 *
 * @package Iamzz
 */
class DingTalk
{
    /**
     * http client
     *
     * @var Client
     */
    private Client $httpClient;
    /**
     * DINGDING robot api uri
     *
     * @var string
     */
    private string $bashUri = 'https://oapi.dingtalk.com/robot/send';
    /**
     * @var string
     */
    private string $accessToken;
    /**
     * @var string
     */
    private string $accessKeySecret;

    /**
     * Dingding constructor.
     *
     * @param string $access_token      钉钉机器人token
     * @param string $access_key_secret 钉钉机器人secret key
     */
    public function __construct(string $access_token, string $access_key_secret = '', array $option = [])
    {
        $this->httpClient      = new Client(['base_uri' => $this->bashUri] + $option);
        $this->accessToken     = $access_token;
        $this->accessKeySecret = $access_key_secret;
    }

    /**
     * 发送消息
     *
     * @param DingTalkMessage $message
     *
     * @return bool
     * @throws GuzzleException|JsonException
     */
    public function send(DingTalkMessage $message): bool
    {
        $headers   = [
            'Content-Type' => 'application/json;charset=utf-8',
        ];
        $request   = new Request('POST', '', $headers, $message->toJson());
        $queryData = [
            'access_token' => $this->accessToken,
        ];

        if ($this->accessKeySecret) {
            $queryData['timestamp'] = floor(microtime(true) * 1000);
            $queryData['sign']      = $this->sign($queryData['timestamp']);
        }
        $response = $this->httpClient->send($request, ['query' => $queryData]);

        $contents = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        return $response->getStatusCode() === 200 && ($contents['errcode'] ?? 1) === 0;
    }

    /**
     * 计算签名
     *
     * @param float $time 签名需要的时间戳
     *
     * @return string
     */
    private function sign(float $time): string
    {
        return urlencode(base64_encode(hash_hmac('sha256', $time . "\n" . $this->accessKeySecret, $this->accessKeySecret, true)));
    }
}