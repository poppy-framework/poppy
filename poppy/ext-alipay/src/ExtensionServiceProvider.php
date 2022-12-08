<?php

declare(strict_types = 1);

namespace Poppy\Extension\Alipay;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * Bootstrap the application events.
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {

    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [
        ];
    }
}
