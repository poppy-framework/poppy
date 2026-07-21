<?php

declare(strict_types = 1);

namespace Poppy\Framework\Helper;

/**
 * 搜素排序
 */
class SearchHelper
{
    /**
     * 获取排序的key
     *
     * @param string $default_order 默认的排序
     * @param array  $allowed       允许的key
     * @param string $input_key     默认的input键
     */
    public static function key(string $default_order, array $allowed = [], string $input_key = '_order'): string
    {
        $order = input($input_key);
        if (!$order) {
            return $default_order;
        }

        $orderKey = $default_order;
        if (false !== strpos($order, '_desc')) {
            $orderKey = str_replace('_desc', '', $order);
        }
        if (false !== strpos($order, '_asc')) {
            $orderKey = str_replace('_asc', '', $order);
        }
        if (in_array($orderKey, $allowed, true)) {
            return $orderKey;
        }

        return $default_order;
    }

    /**
     * 排序类型
     *
     * @param string $key key
     */
    public static function order(string $key = '_order'): string
    {
        $order = (string) input($key);
        if (false !== strpos($order, '_desc')) {
            return 'desc';
        }
        if (false !== strpos($order, '_asc')) {
            return 'asc';
        }

        return 'desc';
    }
}
