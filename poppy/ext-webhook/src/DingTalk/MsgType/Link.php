<?php

declare(strict_types = 1);

namespace Poppy\Extension\Webhook\DingTalk\MsgType;


/**
 * link类型
 *
 */
class Link extends Message
{
    /**
     * @var string
     */
    private string $title;
    /**
     * @var string
     */
    private string $text;
    /**
     * @var string
     */
    private string $messageUrl;

    /**
     * @var string
     */
    private string $picUrl = '';

    public function __construct(string $title, string $text, string $messageUrl)
    {
        $this->type = 'link';

        $this->title      = $title;
        $this->text       = $text;
        $this->messageUrl = $messageUrl;
    }

    /**
     * @inerhitDoc
     */
    public function toJson(): string
    {
        $this->message['link'] = [
            'title'      => $this->title,
            'text'       => $this->text,
            'messageUrl' => $this->messageUrl,
        ];
        if ($this->picUrl) {
            $this->message['link']['picUrl'] = $this->picUrl;
        }

        return parent::toJson();
    }

    /**
     * @param string $picUrl
     * @return Link
     */
    public function setPicUrl(string $picUrl): self
    {
        $this->picUrl = $picUrl;
        return $this;
    }
}