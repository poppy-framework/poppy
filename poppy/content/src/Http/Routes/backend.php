<?php

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\Content\Http\Request\Backend',
], function (Router $router) {
    /* 分类管理
     * ---------------------------------------- */
    $router->any('content', 'ContentController@index')
        ->name('py-content:backend.content.index');
    $router->any('content/establish/{id?}', 'ContentController@establish')
        ->name('py-content:backend.content.establish');
    $router->any('content/delete/{id}', 'ContentController@delete')
        ->name('py-content:backend.content.delete');
});
