<?php

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Demo\Http\Request\Web',
], function (Router $router) {
    $router->any('/', 'HomeController@index');
});
