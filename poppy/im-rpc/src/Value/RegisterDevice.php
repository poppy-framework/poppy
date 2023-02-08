<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

/**
 * 注册设备信息
 */
class RegisterDevice extends BaseValue
{
    protected string $uid = '';

    protected string $pushId = '';

    protected string $deviceType = '';

    protected int $version = 0;

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     * @return RegisterDevice
     */
    public function setUid(string $uid): RegisterDevice
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return string
     */
    public function getPushId(): string
    {
        return $this->pushId;
    }

    /**
     * @param string $pushId
     * @return RegisterDevice
     */
    public function setPushId(string $pushId): RegisterDevice
    {
        $this->pushId = $pushId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceType(): string
    {
        return $this->deviceType;
    }

    /**
     * @param string $deviceType
     * @return RegisterDevice
     */
    public function setDeviceType(string $deviceType): RegisterDevice
    {
        $this->deviceType = strtolower($deviceType);
        return $this;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return RegisterDevice
     */
    public function setVersion(int $version): RegisterDevice
    {
        $this->version = $version;
        return $this;
    }

}