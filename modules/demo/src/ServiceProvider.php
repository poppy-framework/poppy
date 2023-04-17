<?php

namespace Demo;

use Demo\Http\RouteServiceProvider;
use Demo\Listeners\PassportVerify\PassportVerifyListener;
use Demo\Models\DemoGrid;
use Demo\Models\Policies\DemoGridPolicy;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\System\Events\PassportVerifyEvent;

class ServiceProvider extends PoppyServiceProvider
{

    protected array $policies = [
        DemoGrid::class => DemoGridPolicy::class,
    ];

    protected array $listens = [
        PassportVerifyEvent::class => [
            PassportVerifyListener::class,
        ],
    ];

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('demo');
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
