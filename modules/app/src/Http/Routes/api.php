<?php
/*
|--------------------------------------------------------------------------
| Demo
|--------------------------------------------------------------------------
|
*/
Route::group([
    'middleware' => ['cross'],
    'namespace'  => 'App\Http\Request\Api\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('resp/success', 'RespController@success');
    $route->get('resp/error', 'RespController@error');
    $route->get('resp/validator', 'RespController@validator');
    $route->get('resp/401', 'RespController@unAuth');
    $route->get('resp/header', 'RespController@header');
});

Route::group([
    'middleware' => ['api-sso'],
    'namespace'  => 'App\Http\Request\Api\Web',
], function (Illuminate\Routing\Router $route) {
    $route->post('sso/access', 'SsoController@access');
});