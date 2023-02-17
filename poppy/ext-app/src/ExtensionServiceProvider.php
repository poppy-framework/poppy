<?php

declare(strict_types = 1);

namespace Poppy\Extension\App;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Poppy\Extension\App\Classes\AppClient;


/**
 * App 请求
 */
class ExtensionServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * Register the service provider.
     * @return void
     */
    public function register(): void
    {
        $this->registerApp();
    }

    private function registerApp(): void
    {
        $this->app->singleton('poppy.ext.app', function () {
            return new AppClient();
        });
    }
}
