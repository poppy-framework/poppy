<?php

declare(strict_types = 1);

namespace Poppy\Core\Http;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Poppy\Core\Rbac\Middlewares\RbacPermission;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        /* Rbac
         * ---------------------------------------- */
        $router->aliasMiddleware('sys-rbac', RbacPermission::class);
    }
}
