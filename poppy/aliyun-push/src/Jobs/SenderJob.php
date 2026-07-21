<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Jobs;

use Poppy\AliyunPush\Classes\Config\Config;
use Poppy\AliyunPush\Classes\Sender\PushMessage;
use Poppy\AliyunPush\Classes\Sender\PushSender;
use Poppy\AliyunPush\Exceptions\PushException;

/**
 * 推送Job
 */
class SenderJob
{
    /**
     * 推送消息
     */
    protected PushMessage $message;

    /**
     * 推送配置信息
     */
    private Config $config;

    public function __construct(PushMessage $message, Config $config)
    {
        $this->message = $message;
        $this->config  = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws PushException
     */
    public function handle()
    {
        $Sender = new PushSender($this->config);
        $Sender->send($this->message);
    }
}
