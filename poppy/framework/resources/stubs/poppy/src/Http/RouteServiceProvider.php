<?php

declare(strict_types = 1);

namespace DummyNamespace\Http;

use Poppy\Framework\Application\RouteServiceProvider as PoppyFrameworkRouteServiceProvider;
use Route;

class RouteServiceProvider extends PoppyFrameworkRouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */
    protected $namespace = 'DummyNamespace\Http\Request';

    /**
     * Define your route model bindings, pattern filters, etc.
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the module.
     * @return void
     */
    public function map(): void
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the module.
     * These routes all receive session state, CSRF protection, etc.
     * @return void
     */
    protected function mapWebRoutes(): void
    {
        Route::group([
            // todo auth
            'prefix' => 'DummySlug',
        ], function () {
            require_once __DIR__ . '/Routes/web.php';
        });

        Route::group([
            'prefix'     => $this->prefix . '/DummySlug',
            'middleware' => 'backend-auth',
        ], function () {
            require_once __DIR__ . '/Routes/backend.php';
        });
    }

    /**
     * Define the "api" routes for the module.
     * These routes are typically stateless.
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        Route::group([
            // todo auth
            'prefix' => 'api/DummySlug',
        ], function () {
            require_once __DIR__ . '/Routes/api.php';
        });
    }
}
