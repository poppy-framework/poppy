<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Utils;

use Hyperf\Utils\Contracts\Arrayable;

class Response implements Arrayable
{
    public const SUCCESS = 0;
    public const ERROR   = 1;

    protected string $message = '成功';

    protected array $data = [];

    protected int $status = 0;

    /**
     * @param string $message
     * @param array  $data
     * @param int    $status
     */
    public function __construct(string $message, array $data, int $status)
    {
        $this->message = $message;
        $this->data    = $data;
        $this->status  = $status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param string $message
     * @param array  $data
     * @return \Poppy\Im\Rpc\Value\Response
     */
    public static function success(string $message = '操作成功', array $data = []): Response
    {
        return new self($message, $data, self::SUCCESS);
    }

    /**
     * @param string $message
     * @param array  $data
     * @return Response
     */
    public static function error(string $message = '操作失败', array $data = []): Response
    {
        return new self($message, $data, self::ERROR);
    }

    /**
     * @param string $message
     * @param int    $status
     * @param array  $data
     * @return Response
     */
    public static function response(string $message, int $status, array $data = []): Response
    {
        return new self($message, $data, $status);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status'  => $this->status,
            'message' => $this->message,
            'data'    => $this->data,
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}