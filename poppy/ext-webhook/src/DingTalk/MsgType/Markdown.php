<?php
declare(strict_types = 1);

namespace Poppy\Extension\Webhook\DingTalk\MsgType;


/**
 * markdown类型
 *
 */
class Markdown extends Message
{
    /**
     * 首屏会话透出的展示内容
     *
     * @var string
     */
    private string $title;
    /**
     * @var string markdown格式的消息
     */
    private string $text;

    /**
     * Markdown constructor.
     *
     * @param string $title
     * @param string $text
     */
    public function __construct(string $title, string $text)
    {
        $this->type  = 'markdown';
        $this->title = $title;
        $this->text  = $text;
    }

    /**
     * @inerhitDoc
     */
    public function toJson(): string
    {
        $this->message['markdown']['title'] = $this->title;
        $this->message['markdown']['text']  = $this->formatContent($this->text);
        return parent::toJson();
    }
}