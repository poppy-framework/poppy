<?php

namespace Demo\Hooks\System;

use Poppy\Core\Services\Contracts\ServiceArray;

class AuthAccessUser implements ServiceArray
{
    public function key(): string
    {
        return 'user';
    }

    public function data(): array
    {
        return [
            'nickname' => 'nickname',
        ];
    }
}
