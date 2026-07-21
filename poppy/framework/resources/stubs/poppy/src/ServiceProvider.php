<?php

declare(strict_types = 1);

namespace DummyNamespace;

use DummyNamespace\Http\RouteServiceProvider;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @throws ModuleNotFoundException
     */
    public function boot(): void
    {
        parent::boot('DummySlug');
    }

    /**
     * Register the module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
