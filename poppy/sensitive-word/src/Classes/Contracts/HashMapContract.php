<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Classes\Contracts;

/**
 * HashMap
 */
interface HashMapContract
{
    /**
     * @param string $key   key
     * @param mixed  $value value
     * @return mixed
     */
    public function put(string $key, $value);

    /**
     * @param string $key key
     * @return mixed|null
     */
    public function get(string $key);

}