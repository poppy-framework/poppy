<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc;

use Poppy\Im\Rpc\Value\From;
use Poppy\Im\Rpc\Value\Message;
use Poppy\Im\Rpc\Value\Packer;
use Poppy\Im\Rpc\Value\Team;

/**
 * 移动推送
 * @package Poppy\Im\Rpc
 */
interface MobilePushService
{
    /**
     * 注册阿里推送
     *
     * @param array $data
     * @return bool
     */
    public function register(array $data): bool;

    public function notification(From $from, Team $team, Message $message): Packer;

    public function push(Packer $notificationPacker, Team $team, array $targetUid): void;
}