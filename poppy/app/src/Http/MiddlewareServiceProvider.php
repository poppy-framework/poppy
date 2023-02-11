<?php

declare(strict_types = 1);

namespace Poppy\App\Http;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Poppy\App\Http\Middlewares\AppSignMiddleware;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * @param Router $router
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('py-app.sign', AppSignMiddleware::class);
    }
}