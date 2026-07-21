<?php

declare(strict_types = 1);

namespace Poppy\Ad;

use Poppy\Ad\Http\RouteServiceProvider;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{
    protected array $policies = [
        Models\SysAdPlace::class => Models\Policies\AdPlacePolicy::class,
    ];

    /**
     * Bootstrap the module services.
     *
     * @return void
     *
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.ad');
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
