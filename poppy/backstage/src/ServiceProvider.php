<?php

declare(strict_types = 1);

namespace Poppy\Backstage;

use Poppy\Backstage\Classes\Sign\DefaultSignProvider;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\System\Classes\Contracts\ApiSignContract;


class ServiceProvider extends PoppyServiceProvider
{
    use PoppyTrait;

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot(): void
    {
        parent::boot('poppy.backstage');
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register(): void
    {
        $this->app->register(Http\MiddlewareServiceProvider::class);
        $this->app->register(Http\RouteServiceProvider::class);

        $this->registerContracts();
    }


    private function registerContracts(): void
    {
        $this->app->bind('poppy.backstage.sign', function () {
            /** @var ApiSignContract $signProvider */
            $signProvider = config('poppy.backstage.sign_provider') ?: DefaultSignProvider::class;
            return new $signProvider();
        });
    }
}