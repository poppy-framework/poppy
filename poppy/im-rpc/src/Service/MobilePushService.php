<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Service;

use Poppy\Im\Rpc\Value\From;
use Poppy\Im\Rpc\Value\Message;
use Poppy\Im\Rpc\Value\Packer;
use Poppy\Im\Rpc\Value\RegisterDevice;
use Poppy\Im\Rpc\Value\Team;

/**
 * 移动推送
 * @package Poppy\Im\Rpc
 */
interface MobilePushService
{
    /**
     * 注册阿里推送
     * @param RegisterDevice $device
     * @return bool
     */
    public function register(RegisterDevice $device): bool;

    /**
     * 取消ali推送的绑定
     * @param string $uid
     * @param array  $pushIds
     * @return bool
     */
    public function unbind(string $uid, array $pushIds): bool;

    /**
     * 通知信息
     * @param From    $from
     * @param Team    $team
     * @param Message $message
     * @return Packer
     */
    public function notification(From $from, Team $team, Message $message): Packer;

    /**
     * 发送推送
     * @param Packer $notificationPacker
     * @param Team   $team
     * @param array  $targetUid
     * @return bool
     */
    public function push(Packer $notificationPacker, Team $team, array $targetUid): bool;
}