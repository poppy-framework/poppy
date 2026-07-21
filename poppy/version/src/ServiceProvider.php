<?php

declare(strict_types = 1);

namespace Poppy\Version;

use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\Version\Http\RouteServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @throws ModuleNotFoundException
     */
    public function boot(): void
    {
        parent::boot('poppy.version');
    }

    /**
     * Register the module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
