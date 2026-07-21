<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Classes\Sensitive;

use Poppy\SensitiveWord\Classes\Contracts\HashMapContract;

/**
 * 构建hash表
 */
class HashMap implements HashMapContract
{
    protected array $hashTable = [];

    /**
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return mixed
     */
    public function put(string $key, $value): self
    {
        $this->hashTable[$key] = $value;

        return $this;
    }

    /**
     * @param string $key key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->hashTable)) {
            return $this->hashTable[$key];
        }

        return null;
    }

    /**
     * 获取所有key
     */
    public function keys(): array
    {
        return array_keys($this->hashTable);
    }

    /**
     * 获取所有值
     */
    public function values(): array
    {
        return array_values($this->hashTable);
    }
}
