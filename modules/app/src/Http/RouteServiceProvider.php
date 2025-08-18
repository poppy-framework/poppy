<?php

namespace App\Http;

use Route;

class RouteServiceProvider extends \Poppy\Framework\Application\RouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */
    protected $namespace = 'App\Http\Request';

    /**
     * Define the routes for the module.
     * @return void
     */
    public function map(): void
    {

        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the module.
     * These routes are typically stateless.
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'prefix' => 'api/app',
        ], function () {
            require_once __DIR__ . '/Routes/api.php';
        });
    }
}
