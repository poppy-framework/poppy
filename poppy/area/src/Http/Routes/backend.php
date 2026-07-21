<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\Area\Http\Request\Backend',
], function (Router $router) {
    $router->any('/', 'ContentController@index')
        ->name('py-area:backend.content.index');
    $router->any('establish/{id?}', 'ContentController@establish')
        ->name('py-area:backend.content.establish');
    $router->any('delete/{id?}', 'ContentController@delete')
        ->name('py-area:backend.content.delete');
    $router->any('fix', 'ContentController@fix')
        ->name('py-area:backend.content.fix');
});
