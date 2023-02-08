<?php

declare(strict_types = 1);

namespace Poppy\Im\Rpc\Value;

use Throwable;

class Packer
{
    private string $result = '';

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @param string $result
     * @return Packer
     */
    public function setResult(string $result): Packer
    {
        $this->result = $result;
        return $this;
    }

    public function pack($value): self
    {
        $this->result = serialize($value);

        return $this;
    }

    public function unpack($default = null)
    {
        try {
            return unserialize($this->result);
        } catch (Throwable $e) {
            return $default;
        }
    }
}