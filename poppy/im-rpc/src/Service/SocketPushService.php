<?php
declare(strict_types = 1);

namespace Poppy\Im\Rpc\Service;

interface SocketPushService
{
    /**
     * 广播
     * @param array  $data      推送数据
     * @param string $event     event
     * @param string $namespace nsp
     * @return mixed
     */
    public function broadcast(array $data, string $event, string $namespace = '');

    /**
     * 发送信息
     * @param string $event     event
     * @param array  $users     目标用户
     * @param array  $data      发送信息
     * @param string $namespace nsp
     * @return mixed
     */
    public function emit(string $event, array $users, array $data, string $namespace = '');

    /**
     * 是否有客户端
     * @param string $uid
     * @return bool
     */
    public function hasClient(string $uid): bool;
}