<?php

namespace App;

use App\Http\RouteServiceProvider;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\System\Events\PassportVerifyEvent;

class ServiceProvider extends PoppyServiceProvider
{

    protected array $policies = [

    ];

    protected array $listens = [
        PassportVerifyEvent::class => [

        ],
    ];

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('app');
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
