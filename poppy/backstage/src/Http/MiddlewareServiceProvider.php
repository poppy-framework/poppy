<?php

namespace Poppy\Backstage\Http;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('sys-backstage_sign', Middlewares\DefaultBackstageSign::class);

        $router->middlewareGroup('backstage-sign', [
            'api',                   // Api
            'sys-backstage_sign',          // Sign
        ]);

        // 管理中间件
        $router->middlewareGroup('backstage-auth', [
            'mgr-sign',              // Api
            'sys-auth:jwt_backend',  // Auth
            'sys-jwt',               // Pwd Changed
            'sys-ban:backend',       // Ban Backend
            'sys-rbac',              // Permission
        ]);
    }
}