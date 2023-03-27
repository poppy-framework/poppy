<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\Ad\Http\Request\Backend',
], function (Router $router) {
    /* 广告位管理
     * ---------------------------------------- */
    $router->any('place', 'AdPlaceController@index')
        ->name('py-ad:backend.place.index');
    $router->any('place/establish/{id?}', 'AdPlaceController@establish')
        ->name('py-ad:backend.place.establish');
    $router->any('place/delete/{id}', 'AdPlaceController@delete')
        ->name('py-ad:backend.place.delete');

    /* 广告内容管理
     * ---------------------------------------- */
    $router->any('content', 'AdContentController@index')
        ->name('py-ad:backend.content.index');
    $router->any('content/establish/{id?}', 'AdContentController@establish')
        ->name('py-ad:backend.content.establish');
    $router->any('content/delete/{id}', 'AdContentController@delete')
        ->name('py-ad:backend.content.delete');
    $router->any('content/toggle/{id}', 'AdContentController@toggle')
        ->name('py-ad:backend.content.toggle');
});
