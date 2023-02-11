<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\App\Http\Request\Backend',
], function (Router $router) {
    /* 应用管理
     * ---------------------------------------- */
    $router->any('app', 'AppController@index')
        ->name('py-app:backend.app.index');
    $router->any('app/establish/{id?}', 'AppController@establish')
        ->name('py-app:backend.app.establish');
    $router->any('app/status/{id}/{status}', 'AppController@status')
        ->name('py-app:backend.app.status');
});
