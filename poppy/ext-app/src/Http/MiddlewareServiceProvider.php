<?php

declare(strict_types = 1);

namespace Poppy\Extension\App\Http;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Poppy\Extension\App\Http\Middlewares\JsonAppSignMiddleware;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('py-ext-app.sign-json', JsonAppSignMiddleware::class);
    }
}
