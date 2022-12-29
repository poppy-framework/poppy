<?php

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\Category\Http\Request\Backend',
], function (Router $router) {
    /* 分类管理
     * ---------------------------------------- */
    $router->any('category', 'CategoryController@index')
        ->name('py-category:backend.category.index');
    $router->any('category/establish/{id?}', 'CategoryController@establish')
        ->name('py-category:backend.category.establish');
    $router->any('category/delete/{id}', 'CategoryController@delete')
        ->name('py-category:backend.category.delete');
});
