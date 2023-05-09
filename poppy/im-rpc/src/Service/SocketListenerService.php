<?php
declare(strict_types = 1);

namespace Poppy\Im\Rpc\Service;

use Poppy\Im\Rpc\Utils\Response;

interface SocketListenerService
{
    /**
     * @param string $uid
     * @return array
     */
    public function connect(string $uid): array;

    /**
     * @param string $uid
     * @return mixed
     */
    public function disconnect(string $uid);

    /**
     * 聊天
     * @param array $data
     * @return Response
     */
    public function chat(array $data): Response;

    /**
     * 操作
     * @param array $data
     * @return Response
     */
    public function action(array $data): Response;
}