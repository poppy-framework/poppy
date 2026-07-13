<?php

namespace Poppy\MgrPage\Http;

use Illuminate\Routing\Router;
use Poppy\MgrPage\Http\Request\Backend\CaptchaController;
use Poppy\MgrPage\Http\Request\Backend\HomeController;
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
            'prefix'    => $this->prefix,
        ], function (Router $router) {
            $router->any('/', [HomeController::class, 'index'])
                ->middleware('backend-auth')
                ->name('py-mgr-page:backend.home.index');
            $router->any('login', [HomeController::class, 'login'])
                ->middleware('web')
                ->name('py-mgr-page:backend.home.login');
            $router->any('captcha/send', [CaptchaController::class, 'send'])
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
        Route::group([
            'middleware' => 'backend-auth',
            'prefix'     => $this->prefix . '/develop',
        ], function () {
            require_once __DIR__ . '/Routes/develop.php';
        });
    }
}