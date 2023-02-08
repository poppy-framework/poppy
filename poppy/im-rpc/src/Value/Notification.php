<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

/**
 * 通知内容
 */
class Notification extends BaseValue
{
    protected string $operation = 'chat';

    protected string $tid       = '';

    protected string $msgId     = '';

    protected Team $team;

    protected string $title = '';

    protected string $content = '';

    public function __construct()
    {
        $this->team = new Team();
    }

    /**
     * @return string
     */
    public function getTid(): string
    {
        return $this->tid;
    }

    /**
     * @param string $tid
     * @return Notification
     */
    public function setTid(string $tid): Notification
    {
        $this->tid = $tid;
        return $this;
    }

    /**
     * @return string
     */
    public function getMsgId(): string
    {
        return $this->msgId;
    }

    /**
     * @param string $msgId
     * @return Notification
     */
    public function setMsgId(string $msgId): Notification
    {
        $this->msgId = $msgId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Notification
     */
    public function setTitle(string $title): Notification
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Notification
     */
    public function setContent(string $content): Notification
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     * @return Notification
     */
    public function setTeam(Team $team): Notification
    {
        $this->team = $team;
        return $this;
    }
}