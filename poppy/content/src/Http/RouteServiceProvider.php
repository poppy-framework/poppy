<?php

namespace Poppy\Content\Http;

use Route;

class RouteServiceProvider extends \Poppy\Framework\Application\RouteServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     * In addition, it is set as the URL generator's root namespace.
     * @var string
     */
    protected $namespace = 'Poppy\Content\Request';

    /**
     * Define the routes for the module.
     * @return void
     */
    public function map()
    {
        Route::group([
            'prefix'     => $this->prefix . '/py-content',
            'middleware' => 'backend-auth',
        ], function () {
            require_once __DIR__ . '/Routes/backend.php';
        });

        // 排序
        Route::group([
            'middleware' => 'api-sign',
            'prefix'     => 'api_v1/content',
        ], function () {
            require_once __DIR__ . '/Routes/api_v1.php';
        });
    }
}
