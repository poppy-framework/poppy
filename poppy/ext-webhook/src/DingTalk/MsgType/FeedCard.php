<?php
declare(strict_types = 1);

namespace Poppy\Extension\Webhook\DingTalk\MsgType;


/**
 * FeedCard类型
 *
 */
class FeedCard extends Message
{
    /**
     * @var array
     */
    private array $links;

    /**
     * FeedCard constructor.
     *
     * @param array $links
     */
    public function __construct(array $links)
    {
        $this->type  = 'feedCard';
        $this->links = $links;
    }

    /**
     * 最终输出的结构体JSON
     *
     * @return string
     */
    public function toJson():string
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