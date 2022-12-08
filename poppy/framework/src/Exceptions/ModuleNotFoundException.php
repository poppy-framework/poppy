<?php

declare(strict_types = 1);

namespace Poppy\Framework\Exceptions;

use Illuminate\Support\Str;

/**
 * ModuleNotFoundException
 */
class ModuleNotFoundException extends BaseException
{
    /**
     * ModuleNotFoundException constructor.
     * @param string $slug slug
     */
    public function __construct(string $slug)
    {
        $errMsg = '';
        if (!Str::contains($slug, '.')) {
            $errMsg = 'Module after version 2.x must format as `module.' . $slug . '`';
        }
        parent::__construct('Module with slug name [' . $slug . '] not found. ' . $errMsg);
    }
}