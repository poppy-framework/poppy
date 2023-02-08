<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

/**
 * 群信息
 */
class Team extends BaseValue
{
    protected int $id = 0;

    protected string $tid = '';

    protected string $name = '';

    protected string $owner = '';

    protected int $type = 0;

    protected string $icon = '';

    protected string $ext = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Team
     */
    public function setId(int $id): Team
    {
        $this->id = $id;
        return $this;
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
     * @return Team
     */
    public function setTid(string $tid): Team
    {
        $this->tid = $tid;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Team
     */
    public function setName(string $name): Team
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     * @return Team
     */
    public function setOwner(string $owner): Team
    {
        $this->owner = $owner;
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
     * @return Team
     */
    public function setType(int $type): Team
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return Team
     */
    public function setIcon(string $icon): Team
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getExt(): string
    {
        return $this->ext;
    }

    /**
     * @param string $ext
     * @return Team
     */
    public function setExt(string $ext): Team
    {
        $this->ext = $ext;
        return $this;
    }
}