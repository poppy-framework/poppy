<?php

declare(strict_types = 1);

namespace Poppy\Extension\Webhook\DingTalk\MsgType;

/**
 * FeedCard类型
 */
class FeedCard extends Message
{
    private array $links;

    /**
     * FeedCard constructor.
     */
    public function __construct(array $links)
    {
        $this->type  = 'feedCard';
        $this->links = $links;
    }

    /**
     * 最终输出的结构体JSON
     */
    public function toJson(): string
    {
        foreach ($this->links as $link) {
            $this->message['feedCard']['links'][] = [
                'title'      => $link['title'],
                'messageURL' => $link['messageURL'],
                'picURL'     => $link['picURL'],
            ];
        }

        return parent::toJson();
    }
}
