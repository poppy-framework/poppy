<?php

declare(strict_types = 1);

namespace Poppy\Core\Rbac\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Rbac Facade
 */
class PermissionFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'poppy.core.permission';
    }
}
