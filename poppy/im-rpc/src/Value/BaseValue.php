<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Str;

class BaseValue implements Arrayable
{
    /**
     * @return static
     */
    public static function instance()
    {
        return (new static());
    }

    public function __toString(): string
    {
        return json_encode($this->data(), JSON_UNESCAPED_UNICODE);
    }

    public function data(): array
    {
        return get_object_vars($this);
    }

    public function toArray(): array
    {
        $data = $this->data();

        $result = [];
        foreach ($data as $prop => $val) {
            $key = Str::snake($prop);
            if ($val instanceof Arrayable) {
                $value = $val->toArray();
            } else {
                $value = $val;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}