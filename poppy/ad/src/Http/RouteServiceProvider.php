<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http;

use Route;

class RouteServiceProvider extends \Poppy\Framework\Application\RouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */
    protected $namespace = 'Poppy\Ad\Request';

    /**
     * Define the routes for the module.
     * @return void
     */
    public function map():void
    {
        Route::group([
            'prefix'     => $this->prefix . '/py-ad',
            'middleware' => 'backend-auth',
        ], function () {
            require_once __DIR__ . '/Routes/backend.php';
        });
    }
}
