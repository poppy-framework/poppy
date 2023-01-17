<?php

namespace Poppy\MgrPage\Http;

use Illuminate\Routing\Router;
use Route;

class RouteServiceProvider extends \Poppy\Framework\Application\RouteServiceProvider
{
    /**
     * Define the routes for the module.
     * @return void
     */
    public function map(): void
    {
        $this->mapBackendRoutes();

        $this->mapDevRoutes();
    }

    /**
     * Define the "web" routes for the module.
     * These routes all receive session state, CSRF protection, etc.
     * @return void
     */
    protected function mapBackendRoutes(): void
    {
        // backend
        Route::group([
            'prefix' => $this->prefix,
        ], function (Router $router) {
            $router->any('/', 'Poppy\MgrPage\Http\Request\Backend\HomeController@index')
                ->middleware('backend-auth')
                ->name('py-mgr-page:backend.home.index');
            $router->any('login', 'Poppy\MgrPage\Http\Request\Backend\HomeController@login')
                ->middleware('web')
                ->name('py-mgr-page:backend.home.login');

            $router->any('captcha/send', '\Poppy\MgrPage\Http\Request\Backend\CaptchaController@send')
                ->middleware('web')
                ->name('py-mgr-page:backend.captcha.send');
        });

        Route::group([
            'prefix'     => $this->prefix . '/system',
            'middleware' => 'backend-auth',
        ], function () {
            require_once __DIR__ . '/Routes/backend.php';
        });
    }

    /**
     * Define the "web" routes for the module.
     * These routes all receive session state, CSRF protection, etc.
     * @return void
     */
    protected function mapDevRoutes(): void
    {
        // develop
        Route::group([
            'middleware' => 'web',
            'prefix'     => $this->prefix . '/develop',
        ], function (Router $router) {
            $router->any('login', 'Poppy\MgrPage\Http\Request\Develop\PamController@login')
                ->name('py-mgr-page:develop.pam.login');
            $router->get('/', 'Poppy\MgrPage\Http\Request\Develop\CpController@index')
                ->middleware('develop-auth')
                ->name('py-mgr-page:develop.cp.cp');
            $router->any('api/json/{type?}', 'Poppy\MgrPage\Http\Request\Develop\ApiController@json')
                ->name('py-mgr-page:develop.api.json');
        });
        Route::group([
            'middleware' => 'develop-auth',
            'prefix'     => $this->prefix . '/develop',
        ], function () {
            require_once __DIR__ . '/Routes/develop.php';
        });
    }
}