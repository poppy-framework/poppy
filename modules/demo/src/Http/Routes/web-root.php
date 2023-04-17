<?php

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Demo\Http\Request\Web',
], function (Router $router) {
    $router->any('/', 'HomeController@index');
    $router->any('demo', 'HomeController@demo');
    $router->any('output/{info}', 'HomeController@output')
        ->name('demo:web.home.output');
});
