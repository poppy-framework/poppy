<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes;

use AlibabaCloud\SDK\Push\V20160801\Models\BindTagRequest;
use Poppy\AliyunPush\Classes\Sender\BaseClient;

/**
 * @url https://help.aliyun.com/document_detail/30082.html
 */
class BindTag extends BaseClient
{
    /**
     * @param string       $device_type 设备类型 [ANDROID|IOS]
     * @param string       $tag         标签
     * @param string|array $client_key  客户端代码
     */
    public function bindDevice(string $device_type, string $tag, $client_key): bool
    {
        $device_type = strtolower($device_type);

        if ('android' === $device_type) {
            $appKey = $this->androidAppKey;
        }
        else {
            $appKey = $this->iosAppKey;
        }

        if (is_array($client_key)) {
            $client_key = implode(',', $client_key);
        }
        $client             = $this->initClient();
        $request            = new BindTagRequest();
        $request->appKey    = $appKey;
        $request->clientKey = $client_key;
        $request->keyType   = 'DEVICE';
        $request->tagName   = $tag;

        $response     = $client->bindTag($request);
        $this->result = $response->body->toMap();

        return true;
    }
}
