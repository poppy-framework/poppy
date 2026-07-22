<?php

declare(strict_types = 1);

namespace Poppy\Core;

use Illuminate\Console\Scheduling\Schedule;
use Poppy\Core\Listeners\PoppyOptimized\ClearCacheListener;
use Poppy\Framework\Events\PoppyOptimized as PoppyOptimizedEvent;
use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{
    protected array $listens = [
        // poppy
        PoppyOptimizedEvent::class => [
            ClearCacheListener::class,
        ],
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
        parent::boot('poppy.core');

        // poppy assets
        $this->publishes([
            __DIR__ . '/../resources/swagger-ui/' => public_path('docs/swagger-ui/'),
        ], 'poppy-mix');

        $this->publishes([
            __DIR__ . '/../resources/openapi/OpenApi.php' => resource_path('docs/OpenApi.php'),
        ], 'poppy-openapi');
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        // 合并配置
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/core.php', 'poppy.core');

        $this->app->register(Module\ModuleServiceProvider::class);
        $this->app->register(Rbac\RbacServiceProvider::class);
        $this->app->register(Http\MiddlewareServiceProvider::class);

        $this->registerConsole();

        $this->registerSchedule();
    }

    private function registerSchedule()
    {
        app('events')->listen('console.schedule', function (Schedule $schedule) {});
    }

    private function registerConsole()
    {
        // system
        $this->commands([
            Commands\PermissionCommand::class,
            Commands\DocCommand::class,
            Commands\InspectCommand::class,
            Commands\PersistCommand::class,
        ]);
    }
}
