<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes\Sender;

use AlibabaCloud\SDK\Push\V20160801\Models\PushRequest;
use Poppy\AliyunPush\Exceptions\PushException;

/**
 * @url https://help.aliyun.com/knowledge_detail/48089.html
 */
class PushSender extends BaseClient
{
    /**
     * 推送消息
     */
    private PushMessage $message;

    /**
     * 发送 Android 信息
     *
     * @throws PushException
     */
    public function send(PushMessage $message): void
    {
        $this->message = $message;

        $this->checkEnv();

        $client = $this->initClient();

        $query = [
            'appKey'      => $this->isAndroid() ? $this->androidAppKey : $this->iosAppKey,
            'pushType'    => $message->getPushType(),
            'deviceType'  => $this->isAndroid() ? 'ANDROID' : 'iOS',
            'title'       => $message->getTitle(),
            'body'        => $message->getBody(),
            'target'      => $message->getTarget(),
            'targetValue' => $message->getTargetValue(),
        ];

        $queryExtend = $message->getQuery();

        $query = array_merge($query, $queryExtend['base'] ?? []);
        if ($this->isIos()) {
            $query = array_merge($query, [
                'iOSExtParameters' => $message->getExtParameters() ?: '{}',
                'iOSApnsEnv'       => is_production() ? 'PRODUCT' : 'DEV',
            ]);
            $query = array_merge($query, $queryExtend['ios'] ?? []);
        }

        if ($this->isAndroid() && $this->isNotice()) {
            $query = array_merge($query, [
                'androidExtParameters'             => $message->getExtParameters(),
                'androidNotificationChannel'       => $this->androidChannel,
                'androidNotificationHuaweiChannel' => 'NORMAL',// NORMAL：服务与通讯类消息LOW：资讯营销类消息
                'androidNotificationHonorChannel'  => 'NORMAL',// NORMAL：服务与通讯类消息LOW：资讯营销类消息
                'androidNotificationVivoChannel'   => '1',// 1：系统类消息0：运营类消息（默认）
            ]);
            if ($this->androidActivity) {
                $query += [
                    'androidOpenType'      => 'ACTIVITY',
                    'androidActivity'      => $this->androidActivity,
                    'androidPopupActivity' => $this->androidActivity,
                    'androidPopupTitle'    => $message->getTitle(),
                    'androidPopupBody'     => $message->getBody(),
                    'storeOffline'         => true,
                ];
            }
            $query = array_merge($query, $queryExtend['android'] ?? []);
        }
        $request = new PushRequest($query);

        $response     = $client->push($request);
        $this->result = $response->body->toMap();
    }

    /**
     * @throws PushException
     */
    private function checkEnv()
    {
        if ($this->isAndroid()) {
            if (!$this->androidAppKey) {
                throw new PushException('Android 应用 KEY 未设置');
            }

            if (!$this->androidChannel) {
                throw new PushException('Android 应用通知频道未设置');
            }
        }
        if (!$this->iosAppKey && $this->isIos()) {
            throw new PushException('IOS 应用KEY 未设置');
        }
    }

    /**
     * 是否发送 Android 消息
     */
    private function isAndroid(): bool
    {
        return PushMessage::DEVICE_TYPE_ANDROID === $this->message->getDeviceType();
    }

    /**
     * 是否发送IOS 消息
     */
    private function isIos(): bool
    {
        return PushMessage::DEVICE_TYPE_IOS === $this->message->getDeviceType();
    }

    /**
     * 是否是通知
     */
    private function isNotice(): bool
    {
        return PushMessage::PUSH_TYPE_NOTICE === $this->message->getPushType();
    }
}
