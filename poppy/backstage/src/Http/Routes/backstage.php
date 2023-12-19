<?php

use Illuminate\Routing\Router;

Route::group([
    'middleware' => 'backstage-sign',
    'namespace'  => 'Poppy\Backstage\Http\Request\Api',
], function (Router $router) {
    // Auth
    $router->post('auth/login', 'AuthController@login');
});

Route::group([
    'middleware' => 'backstage-auth',
    'namespace'  => 'Poppy\Backstage\Http\Request\Api',
], function (Router $router) {
    // 用户信息
    $router->get('home/menu', 'HomeController@menu');
    $router->get('home/setting', 'HomeController@setting');
    $router->post('home/setting', 'HomeController@setting');
    $router->get('auth/access', 'AuthController@access');
});