<?php

namespace Poppy\AliyunPush\Tests\Sample;

use Illuminate\Notifications\Notification;
use Poppy\AliyunPush\Channels\AliPushChannel;
use Poppy\AliyunPush\Contracts\AliPushChannel as AliPushChannelContract;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Exceptions\FakerException;


class IosTagBoyPushNotification extends Notification implements AliPushChannelContract
{

    /**
     * Get the notification's delivery channels.
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [AliPushChannel::class];
    }

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function toAliPush(): array
    {
        return [
            'broadcast_type'    => 'tag',
            'device_type'       => 'ios|notice',
            'title'             => 'Notice.[tag-boy]' . py_faker()->sentence,
            'content'           => 'Content.' . py_faker()->sentences(3, true),
            'registration_tags' => 'boy',
        ];
    }
}
