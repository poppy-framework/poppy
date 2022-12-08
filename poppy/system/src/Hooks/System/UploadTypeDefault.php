<?php

declare(strict_types = 1);

namespace Poppy\System\Hooks\System;

use Poppy\Core\Services\Contracts\ServiceArray;
use Poppy\System\Classes\File\DefaultFileProvider;

class UploadTypeDefault implements ServiceArray
{

    public function key(): string
    {
        return 'default';
    }

    public function data(): array
    {
        return [
            'title'    => '默认(uploads 目录下)',
            'provider' => DefaultFileProvider::class,
        ];
    }
}