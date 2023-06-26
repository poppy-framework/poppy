<?php
declare(strict_types = 1);

namespace Poppy\Extension\Webhook\DingTalk\MsgType;

use Poppy\Extension\Webhook\Contracts\DingTalkMessage as DingTalkMessageContract;

/**
 * Class Message
 */
abstract class Message implements DingTalkMessageContract
{
    /**
     * 消息类型
     * @var string
     */
    protected string $type;

    /**
     * 被@人的手机号（在content里添加@人的手机号）
     *
     * @var array
     */
    protected array $atMobiles = [];

    /**
     * 是否@所有人
     *
     * @var bool
     */
    protected bool $isAll = false;

    /**
     * 最终消息结构体
     *
     * @var array
     */
    protected array $message = [];

    /**
     * 设置需要at的人，默认只有Text和Markdown支持
     * @param array $at
     * @return Message
     */
    public function setAtMobiles(array $at): self
    {
        $this->atMobiles = $at;
        return $this;
    }

    /**
     * 是否at全体成员
     *
     * @param bool $is_all
     * @return Message
     */
    public function setIsAll(bool $is_all): self
    {
        $this->isAll = $is_all;
        return $this;
    }

    /**
     * 最终输出的结构体JSON
     * @return string
     */
    public function toJson(): string
    {
        $this->message['msgtype'] = $this->type;
        //仅有text和markdown类型的消息会用到@信息
        if ($this->type === 'text' or $this->type === 'markdown') {
            if ($this->isAll === true) {
                $this->message['at']['isAtAll'] = $this->isAll;
            }
            if (!empty($this->atMobiles)) {
                $this->message['at']['atMobiles'] = array_values($this->atMobiles);
            }
        }
        $this->message = array_filter($this->message);
        return json_encode($this->message, JSON_THROW_ON_ERROR);
    }

    /**
     * 格式化消息体
     *
     * @param string $content
     *
     * @return string
     */
    protected function formatContent(string $content): string
    {
        if (empty($this->atMobiles)) {
            return $content;
        }
        $formatContent = '';
        $isEscape      = false;
        $isParam       = false;
        $paramKey      = '';
        for ($i = 0; $i < strlen($content); $i++) {
            switch ($content[$i]) {
                case '\\':
                    $isEscape = true;
                    break;
                case '{':
                    if ($isEscape) {
                        $formatContent .= $content[$i];
                        $isEscape      = false;
                    } else {
                        $isParam = true;
                    }
                    break;
                case '}':
                    if ($isParam) {
                        $formatContent .= '@' . $this->atMobiles[$paramKey];
                        $isParam       = false;
                        $paramKey      = '';
                    } else {
                        $formatContent .= $content[$i];
                    }
                    break;
                default:
                    if ($isParam) {
                        $paramKey .= $content[$i];
                    } else {
                        $formatContent .= $content[$i];
                    }
            }
        }
        return $formatContent;
    }
}