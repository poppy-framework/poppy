<?php
/*
|--------------------------------------------------------------------------
| Demo
|--------------------------------------------------------------------------
|
*/
Route::group([
    'namespace'  => 'Demo\Http\Request\Api\App',
], function (Illuminate\Routing\Router $route) {
    $route->get('demo/index', 'DemoController@index');
});