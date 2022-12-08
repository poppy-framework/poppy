<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes;

use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Poppy\AliyunPush\Classes\Sender\BaseClient;
use Poppy\AliyunPush\Exceptions\PushException;

/**
 * @url https://help.aliyun.com/document_detail/30082.html
 */
class BindTag extends BaseClient
{
    /**
     * @param string       $device_type 设备类型 [ANDROID|IOS]
     * @param string       $tag         标签
     * @param string|array $client_key  客户端代码
     * @return bool
     * @throws ClientException
     * @throws PushException
     * @throws ServerException
     */
    public function bindDevice(string $device_type, string $tag, $client_key): bool
    {
        $device_type = strtolower($device_type);

        if ($device_type === 'android') {
            $appKey = $this->androidAppKey;
        }
        else {
            $appKey = $this->iosAppKey;
        }

        if (is_array($client_key)) {
            $client_key = implode(',', $client_key);
        }
        $this->initClient();
        $this->result = $this->rpc()
            ->action('BindTag')
            ->options([
                'query' => [
                    'AppKey'    => $appKey,
                    'ClientKey' => $client_key,
                    'KeyType'   => "DEVICE",
                    'TagName'   => $tag,
                ],
            ])
            ->request();
        return true;
    }
}