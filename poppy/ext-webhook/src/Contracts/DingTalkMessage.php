<?php

declare(strict_types = 1);

namespace Poppy\Extension\Webhook\Contracts;

/**
 * 机器人消息接口
 * Interface MessageInterface
 */
interface DingTalkMessage
{
    /**
     * 最终输出的结构体JSON
     */
    public function toJson(): string;
}
