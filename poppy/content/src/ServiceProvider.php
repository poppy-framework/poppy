<?php

declare(strict_types = 1);

namespace Poppy\Content;

use Poppy\Content\Http\RouteServiceProvider;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.content');
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
