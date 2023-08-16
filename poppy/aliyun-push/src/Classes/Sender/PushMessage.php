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
     * @var string
     */
    private string $deviceType;

    /**
     * @var string 标题
     */
    private string $title;

    /**
     * Android推送时通知的内容/消息的内容；iOS消息/通知内容
     * @var string
     */
    private string $body;

    /**
     * 推送类型
     * @var string
     */
    private string $pushType;

    /**
     * 推送目标
     * @var string
     */
    private string $target;

    /**
     * @var string
     */
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
     * @var string
     */
    private string $targetValue;

    /**
     * @return string
     */
    public function getDeviceType(): string
    {
        return $this->deviceType;
    }

    /**
     * @param string $deviceType
     */
    public function setDeviceType(string $deviceType): void
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return string
     */
    public function getExtParameters(): string
    {
        return $this->extParameters;
    }

    /**
     * @param string $extParameters
     */
    public function setExtParameters(string $extParameters): void
    {
        $this->extParameters = $extParameters;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getPushType(): string
    {
        return $this->pushType;
    }

    /**
     * @param mixed $pushType
     */
    public function setPushType($pushType): self
    {
        $this->pushType = $pushType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetValue(): string
    {
        return $this->targetValue;
    }

    /**
     * @param mixed $targetValue
     */
    public function setTargetValue($targetValue): self
    {
        $this->targetValue = $targetValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }


}