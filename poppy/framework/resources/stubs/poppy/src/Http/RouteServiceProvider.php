<?php

namespace DummyNamespace\Http;

use Illuminate\Routing\Router;
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
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the module.
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the module.
     * These routes all receive session state, CSRF protection, etc.
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            // todo auth
            'prefix' => 'DummySlug',
        ], function (Router $route) {
            require_once poppy_path('DummySlug', 'src/Http/Routes/web.php');
        });

        Route::group([
            'prefix'     => $this->prefix . '/DummySlug',
            'middleware' => 'backend-auth',
        ], function (Router $route) {
            require_once poppy_path('DummySlug', 'src/Http/Routes/backend.php');
        });
    }

    /**
     * Define the "api" routes for the module.
     * These routes are typically stateless.
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            // todo auth
            'prefix' => 'api/DummySlug',
        ], function (Router $route) {
            require_once poppy_path('DummySlug', 'src/Http/Routes/api.php');
        });
    }
}
