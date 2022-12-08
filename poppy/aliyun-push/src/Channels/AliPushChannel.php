<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Channels;

use Illuminate\Notifications\Notification;
use Poppy\AliyunPush\Classes\AliPush;
use Poppy\AliyunPush\Classes\Config\Config;
use Poppy\AliyunPush\Contracts\AliPushChannel as AliPushChannelContract;
use Poppy\AliyunPush\Exceptions\PushException;
use Poppy\Framework\Exceptions\ApplicationException;


/**
 * 阿里推送频道
 */
class AliPushChannel
{
    /**
     * Send the given notification.
     * @param mixed                               $notifiable
     * @param Notification|AliPushChannelContract $notification
     * @throws PushException
     * @throws ApplicationException
     */
    public function send($notifiable, Notification $notification)
    {
        $notify = $notification->toAliPush();
        if (!$notify) {
            return;
        }

        $Push = AliPush::getInstance()->setConfig(Config::default());
        if (!$Push->send($notify)) {
            throw (new ApplicationException($Push->getError()))->setContext([
                'notify' => $notify,
            ]);
        }
    }
}