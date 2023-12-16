<?php

use Illuminate\Routing\Router;

Route::group([
    'middleware' => 'backstage-sign',
    'namespace'  => 'Poppy\Backstage\Http\Request\Api',
], function (Router $router) {
    // Auth
    $router->any('auth/login', 'AuthController@login');
});

Route::group([
    'middleware' => 'backstage-auth',
    'namespace'  => 'Poppy\Backstage\Http\Request\Api',
], function (Router $router) {
    // 用户信息
    $router->any('home/menu', 'HomeController@menu');
    $router->any('auth/access', 'AuthController@access');
});