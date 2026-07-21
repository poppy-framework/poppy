<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes\Sender;

/**
 * 发送的消息
 */
class PushMessage
{
    public const DEVICE_TYPE_ANDROID = 'ANDROID';
    public const DEVICE_TYPE_IOS     = 'IOS';

    public const PUSH_TYPE_MESSAGE = 'MESSAGE';
    public const PUSH_TYPE_NOTICE  = 'NOTICE';

    public const TARGET_DEVICE  = 'DEVICE';
    public const TARGET_ACCOUNT = 'ACCOUNT';
    public const TARGET_ALIAS   = 'ALIAS';
    public const TARGET_TAG     = 'TAG';
    public const TARGET_ALL     = 'ALL';

    public const TARGET_VALUE_ALL = 'ALL';

    /**
     * 设备类型
     */
    private string $deviceType;

    /**
     * @var string 标题
     */
    private string $title;

    /**
     * Android推送时通知的内容/消息的内容；iOS消息/通知内容
     */
    private string $body;

    /**
     * 推送类型
     */
    private string $pushType;

    /**
     * 推送目标
     */
    private string $target;

    private string $extParameters = '';

    /**
     * @var array 附加的推送消息
     */
    private array $query = [
        'base'    => [],
        'android' => [],
        'ios'     => [],
    ];

    /**
     * 推送 Target 值
     */
    private string $targetValue;

    public function getDeviceType(): string
    {
        return $this->deviceType;
    }

    public function setDeviceType(string $deviceType): void
    {
        $this->deviceType = $deviceType;
    }

    public function getExtParameters(): string
    {
        return $this->extParameters;
    }

    public function setExtParameters(string $extParameters): void
    {
        $this->extParameters = $extParameters;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody($body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getPushType(): string
    {
        return $this->pushType;
    }

    public function setPushType($pushType): self
    {
        $this->pushType = $pushType;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget($target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getTargetValue(): string
    {
        return $this->targetValue;
    }

    public function setTargetValue($targetValue): self
    {
        $this->targetValue = $targetValue;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function setQuery(array $query): void
    {
        $this->query = $query;
    }
}
