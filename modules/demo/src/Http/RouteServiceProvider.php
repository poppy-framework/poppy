<?php

namespace Demo\Http;

use Route;

class RouteServiceProvider extends \Poppy\Framework\Application\RouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Demo\Http\Request';

    /**
     * Define the routes for the module.
     */
    public function map(): void
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the module.
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::group([
            'prefix' => 'demo',
        ], function () {
            require_once __DIR__ . '/Routes/web.php';
        });

        Route::group([], function () {
            require_once __DIR__ . '/Routes/web-root.php';
        });

        Route::group([
            'prefix'     => $this->prefix . '/demo',
            'middleware' => 'backend-auth',
        ], function () {
            require_once __DIR__ . '/Routes/backend.php';
        });
    }

    /**
     * Define the "api" routes for the module.
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'prefix' => 'api/demo',
        ], function () {
            require_once __DIR__ . '/Routes/api.php';
        });
        Route::group([
            'prefix'     => 'api/app/demo',
            'middleware' => 'py-ext-app.sign-json',
        ], function () {
            require_once __DIR__ . '/Routes/api-app.php';
        });
    }
}
