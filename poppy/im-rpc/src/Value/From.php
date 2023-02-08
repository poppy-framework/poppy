<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

class From extends BaseValue
{
    protected int $id = 0;

    protected string $uid = '';

    protected string $name = '';

    protected string $avatar = '';

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
     * @return From
     */
    public function setId(int $id): From
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     * @return From
     */
    public function setUid(string $uid): From
    {
        $this->uid = $uid;
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
     * @return From
     */
    public function setName(string $name): From
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return From
     */
    public function setAvatar(string $avatar): From
    {
        $this->avatar = $avatar;
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
     * @return From
     */
    public function setExt(string $ext): From
    {
        $this->ext = $ext;
        return $this;
    }
}