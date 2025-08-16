<?php

use Illuminate\Routing\Router;


Route::group([
    'namespace' => 'Poppy\MgrPage\Http\Request\Develop',
], function (Router $router) {
    /* Pam
     * ---------------------------------------- */
    $router->any('/', 'HomeController@index')
        ->name('py-mgr-page:develop.home.cp');
    $router->any('optimize', 'HomeController@optimize')
        ->name('py-mgr-page:develop.home.optimize');

    /* Env
     * ---------------------------------------- */
    $router->get('env/phpinfo', 'EnvController@phpinfo')
        ->name('py-mgr-page:develop.env.phpinfo');
    $router->get('env/db', 'EnvController@db')
        ->name('py-mgr-page:develop.env.db');

    /* Log
     * ---------------------------------------- */
    $router->any('log', 'LogController@index')
        ->name('py-mgr-page:develop.log.index');
});
