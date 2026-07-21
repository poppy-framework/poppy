<?php

use Illuminate\Routing\Router;
use Poppy\MgrPage\Http\Request\Develop\EnvController;
use Poppy\MgrPage\Http\Request\Develop\HomeController;
use Poppy\MgrPage\Http\Request\Develop\LogController;

Route::group([], function (Router $router) {
    /* Pam
     * ---------------------------------------- */
    $router->any('/', [HomeController::class, 'index'])
        ->name('py-mgr-page:develop.home.cp');
    $router->any('optimize', [HomeController::class, 'optimize'])
        ->name('py-mgr-page:develop.home.optimize');

    /* Env
     * ---------------------------------------- */
    $router->get('env/phpinfo', [EnvController::class, 'phpinfo'])
        ->name('py-mgr-page:develop.env.phpinfo');
    $router->get('env/db', [EnvController::class, 'db'])
        ->name('py-mgr-page:develop.env.db');

    /* Log
     * ---------------------------------------- */
    $router->any('log', [LogController::class, 'index'])
        ->name('py-mgr-page:develop.log.index');
});
