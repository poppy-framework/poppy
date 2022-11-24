<?php

namespace Poppy\Core\Redis;

use Throwable;

/**
 * @mixin RdsNative
 */
class RdsDb
{

    private static array $handleRepo;

    /**
     * @var RdsNative
     */
    private RdsNative $handler;

    /**
     * Handle constructor.
     * @param string $database
     * @param string $tag 4.1 支持标签化的缓存
     */
    public function __construct(string $database = '', string $tag = '')
    {
        $database      = $database ?: 'default';
        $config        = config('database.redis.' . $database);
        $this->handler = new RdsNative($config, $tag);
    }

    /**
     * 数据库单例
     * @param string $db  数据库
     * @param string $tag 标签
     * @return mixed|RdsDb
     */
    public static function instance(string $db = 'default', string $tag = '')
    {
        $key = $db . ($tag ? '-' . $tag : '');
        if (!isset(self::$handleRepo[$key])) {
            self::$handleRepo[$key] = new self($db, $tag);
        }
        return self::$handleRepo[$key];
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->handler->$method(...$arguments);
    }

    public function __destruct()
    {
        try {
            $this->handler->disconnect();
        } catch (Throwable $e) {

        }
    }

    public static function __callStatic($method, $arguments)
    {
        return (new self)->$method(...$arguments);
    }
}