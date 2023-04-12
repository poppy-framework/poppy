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

    /* clockwork
     * ---------------------------------------- */
    $router->any('clockwork', 'ClockworkController@index')
        ->name('py-mgr-page:develop.clockwork.index');
    $router->any('clockwork/report', 'ClockworkController@report')
        ->name('py-mgr-page:develop.clockwork.report');

    /* ApiDoc
     * ---------------------------------------- */
    $router->any('api/field/{type}/{field}', 'ApiController@field')
        ->name('py-mgr-page:develop.api.field');
    $router->any('api/login', 'ApiController@login')
        ->name('py-mgr-page:develop.api.login');
    $router->any('api/{type?}', 'ApiController@index')
        ->name('py-mgr-page:develop.api.index');


    // progress
    $router->any('progress', 'ProgressController@index')
        ->name('py-mgr-page:develop.progress.index');
    $router->any('progress/lists', 'ProgressController@lists')
        ->name('py-mgr-page:develop.progress.lists');
});
