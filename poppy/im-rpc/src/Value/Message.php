<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

/**
 * 消息内容
 */
class Message extends BaseValue
{
    protected int $msgId = 0;

    protected string $from = '';

    protected string $to = '';

    protected string $body = '';

    protected int $type = 0;

    protected string $session = '';

    /**
     * @return int
     */
    public function getMsgId(): int
    {
        return $this->msgId;
    }

    /**
     * @param int $msgId
     * @return Message
     */
    public function setMsgId(int $msgId): Message
    {
        $this->msgId = $msgId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return Message
     */
    public function setFrom(string $from): Message
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return Message
     */
    public function setTo(string $to): Message
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Message
     */
    public function setBody(string $body): Message
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Message
     */
    public function setType(int $type): Message
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSession(): string
    {
        return $this->session;
    }

    /**
     * @param string $session
     * @return Message
     */
    public function setSession(string $session): Message
    {
        $this->session = $session;
        return $this;
    }
}