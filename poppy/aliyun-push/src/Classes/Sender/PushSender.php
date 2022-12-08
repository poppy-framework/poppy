<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes\Sender;

use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Poppy\AliyunPush\Exceptions\PushException;

/**
 * @url https://help.aliyun.com/knowledge_detail/48089.html
 */
class PushSender extends BaseClient
{
    /**
     * 推送消息
     * @var PushMessage
     */
    private PushMessage $message;

    /**
     * 发送 Android 信息
     * @param PushMessage $message
     * @throws ClientException
     * @throws PushException
     * @throws ServerException
     */
    public function send(PushMessage $message)
    {
        $this->message = $message;

        $this->checkEnv();
        $query = [
            'AppKey'      => $this->isAndroid() ? $this->androidAppKey : $this->iosAppKey,
            'PushType'    => $message->getPushType(),
            'DeviceType'  => $this->isAndroid() ? 'ANDROID' : "iOS",
            'Title'       => $message->getTitle(),
            'Body'        => $message->getBody(),
            'Target'      => $message->getTarget(),
            'TargetValue' => $message->getTargetValue(),
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
                'AndroidExtParameters'       => $message->getExtParameters(),
                'AndroidNotificationChannel' => $this->androidChannel,
            ]);
            if ($this->androidActivity) {
                $query += [
                    'AndroidOpenType'      => 'ACTIVITY',
                    'AndroidActivity'      => $this->androidActivity,
                    'AndroidPopupActivity' => $this->androidActivity,
                    'AndroidPopupTitle'    => $message->getTitle(),
                    'AndroidPopupBody'     => $message->getBody(),
                    'StoreOffline'         => true,
                ];
            }
            $query = array_merge($query, $queryExtend['android'] ?? []);
        }
        $this->initClient();
        $this->result = $this->rpc()
            ->action('Push')
            ->options([
                'query' => $query,
            ])
            ->request();
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
        if ($this->isIos()) {
            if (!$this->iosAppKey) {
                throw new PushException('IOS 应用KEY 未设置');
            }
        }
    }


    /**
     * 是否发送 Android 消息
     * @return bool
     */
    private function isAndroid(): bool
    {
        return $this->message->getDeviceType() === PushMessage::DEVICE_TYPE_ANDROID;
    }

    /**
     * 是否发送IOS 消息
     * @return bool
     */
    private function isIos(): bool
    {
        return $this->message->getDeviceType() === PushMessage::DEVICE_TYPE_IOS;
    }


    /**
     * 是否是通知
     * @return bool
     */
    private function isNotice(): bool
    {
        return $this->message->getPushType() === PushMessage::PUSH_TYPE_NOTICE;
    }
}