<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss;

use Poppy\AliyunOss\Http\RouteServiceProvider;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     *
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.aliyun-oss');
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
