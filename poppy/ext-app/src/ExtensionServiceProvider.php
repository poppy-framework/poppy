<?php

declare(strict_types = 1);

namespace Poppy\Extension\App;

use Illuminate\Support\ServiceProvider;
use Poppy\Extension\App\Classes\AppClient;
use Poppy\Extension\App\Http\MiddlewareServiceProvider;

/**
 * App 请求
 */
class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(MiddlewareServiceProvider::class);
        $this->registerApp();
    }

    private function registerApp(): void
    {
        $this->app->singleton('poppy.ext.app', function () {
            return new AppClient();
        });
    }
}
