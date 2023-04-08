<?php

namespace Poppy\MgrPage\Http;


use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('py-mgr-lifetime', Middlewares\InterruptLifetime::class);

        $router->middlewareGroup('develop-auth', [
            'web',
            'sys-site_open',
            'sys-auth:develop',
            'sys-auth_session',
            'sys-rbac',
        ]);

        $router->middlewareGroup('backend-auth', [
            'web',
            'sys-auth:backend',
            'sys-auth_session',
            'sys-ban:backend',
            'sys-rbac',
            'py-mgr-lifetime',
        ]);
    }
}