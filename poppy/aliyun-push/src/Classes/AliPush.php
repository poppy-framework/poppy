<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush\Classes;

use Poppy\AliyunPush\Classes\Config\Config;
use Poppy\AliyunPush\Classes\Sender\PushMessage;
use Poppy\AliyunPush\Exceptions\PushException;
use Poppy\AliyunPush\Jobs\SenderJob;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\StrHelper;

/**
 * @url https://help.aliyun.com/document_detail/30082.html
 */
class AliPush
{

    use AppTrait;

    /**
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * @var int
     */
    private int $cutNum = 1000;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var string
     */
    private string $target;

    /**
     * @var
     */
    private $regTags;

    /**
     * @var mixed
     */
    private $title;

    /**
     * @var mixed|string
     */
    private $body;

    /**
     * @var false|string
     */
    private $extras;

    /**
     * 附加的查询项目
     * @var array[]
     */
    private array $query = [];

    /**
     * 发送
     * @param array $params 参数
     * @return bool
     * @throws PushException
     */
    public function send(array $params): bool
    {
        if (empty($params)) {
            return $this->setError('请求参数为空, 无法发送通知');
        }
        $params = $this->compat($params);

        $registrationIds = $params['registration_ids'] ?? [];
        $this->regTags   = $params['registration_tags'] ?? '';
        $this->target    = strtoupper($params['target'] ?? '');
        $this->title     = $params['title'];
        $this->body      = $params['body'] ?? '';
        $this->extras    = json_encode($params['extra'] ?? [], JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->query     = $params['query'] ?? ['base' => [], 'android' => [], 'ios' => [],];

        $devices = StrHelper::parseKey(strtolower($params['device_type'] ?? 'android|notice;ios|notice'));

        if (!count($devices)) {
            return $this->setError('未指定发送设备以及发送类型');
        }

        // send ios
        $iosPushType = strtoupper($devices['ios'] ?? '');
        $iosIds      = $registrationIds['ios'] ?? [];
        $broadcasts  = [];
        if ($iosIds && $iosPushType && config('poppy.aliyun-push.ios_is_open')) {
            $broadcasts = array_merge($broadcasts, $this->toBatches($iosPushType, $iosIds, PushMessage::DEVICE_TYPE_IOS));
        }

        // send android
        $androidPushType = strtoupper($devices['android'] ?? '');
        $androidIds      = $registrationIds['android'] ?? [];
        if ($androidIds && $androidPushType && config('poppy.aliyun-push.android_is_open')) {
            $broadcasts = array_merge($broadcasts, $this->toBatches($androidPushType, $androidIds, PushMessage::DEVICE_TYPE_ANDROID));
        }

        // 发送消息
        collect($broadcasts)->each(function ($message) {
            if ($message instanceof PushMessage) {
                dispatch(new SenderJob($message, $this->config));
            }
        });
        return true;
    }

    public function setConfig(Config $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 获取实例
     * @return self
     */
    final public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 返回批量处理过的通知数据
     * @param string $push_type
     * @param array  $ids ID
     * @param string $device_type
     * @return array
     * @throws PushException
     */
    private function toBatches(string $push_type, array $ids, string $device_type = ''): array
    {
        $broadcasts = [];
        $message    = new PushMessage();
        if (!$this->title) {
            throw new PushException('通知标题不能为空');
        }
        $message->setTitle($this->title);
        $message->setTarget($this->target);
        $message->setPushType($push_type);

        /* 消息不支持 extParams , 消息的 body 体是Json Map 类型的数据
         * ---------------------------------------- */
        if ($push_type === PushMessage::PUSH_TYPE_MESSAGE) {
            $message->setBody($this->extras);
        }
        elseif ($push_type === PushMessage::PUSH_TYPE_NOTICE) {
            if (!$this->body) {
                throw new PushException('通知内容不能为空');
            }
            $message->setBody($this->body);
            $message->setExtParameters($this->extras);
        }
        else {
            throw new PushException('推送类型仅支持 MESSAGE/NOTICE');
        }

        $message->setDeviceType($device_type);
        $message->setQuery($this->query);

        switch ($this->target) {
            // 设备分批
            case PushMessage::TARGET_DEVICE;
                if (!count($ids)) {
                    throw new PushException('用户设备号不能为空');
                }
                $regs = array_chunk($ids, $this->cutNum);
                foreach ($regs as $reg) {
                    $strIds       = implode(',', $reg);
                    $broadcasts[] = (clone $message)
                        ->setTargetValue($strIds);
                }
                break;
            case PushMessage::TARGET_TAG;
                if (!$this->regTags) {
                    throw new PushException('用户标签不能为空');
                }
                $broadcasts[] = (clone $message)
                    ->setTargetValue($this->regTags);
                break;
            case PushMessage::TARGET_ALL;
                $broadcasts[] = (clone $message)->setTargetValue(PushMessage::TARGET_VALUE_ALL);
                break;
        }
        return $broadcasts;
    }

    private function compat($params)
    {
        if (isset($params['broadcast_type'])) {
            $params['target'] = $params['broadcast_type'];
        }
        if (isset($params['content'])) {
            $params['body'] = $params['content'];
        }
        unset($params['broadcast_type'], $params['content']);
        return $params;
    }
}