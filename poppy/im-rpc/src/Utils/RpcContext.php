<?php
declare(strict_types = 1);

namespace Poppy\Im\Rpc\Utils;

use Hyperf\Rpc\Context;

class RpcContext
{
    public static function setUid($value): void
    {
        self::set(self::keyGenerate(__FUNCTION__), $value);
    }

    public static function getUid()
    {
        return self::get(self::keyGenerate(__FUNCTION__));
    }

    /**
     * @param $value
     * @return void
     */
    public static function setUser($value): void
    {
        self::set(self::keyGenerate(__FUNCTION__), $value);
    }

    /**
     * @param $default
     * @return array|\ArrayAccess|mixed
     */
    public static function getUser($default = null)
    {
        return self::get(self::keyGenerate(__FUNCTION__), $default);
    }

    /**
     * @param int $value
     * @return void
     */
    public static function setAppId(int $value): void
    {
        self::set(self::keyGenerate(__FUNCTION__), $value);
    }

    /**
     * @param int $default
     * @return int
     */
    public static function getAppId(int $default = 0): int
    {
        return (int) self::get(self::keyGenerate(__FUNCTION__), $default);
    }

    /**
     * @param string $key
     * @param        $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        (new Context())->set($key, $value);
    }

    /**
     * @param string $key
     * @param        $default
     * @return array|\ArrayAccess|mixed
     */
    public static function get(string $key, $default = null)
    {
        return (new Context())->get($key, $default);
    }

    /**
     * @param string $function
     * @return string
     */
    private static function keyGenerate(string $function): string
    {
        $key = str_replace(['get', 'set'], '', $function);
        return __CLASS__ . ':' . $key;

    }
}