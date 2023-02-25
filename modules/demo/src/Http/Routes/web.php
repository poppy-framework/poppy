<?php

use Illuminate\Routing\Router;


Route::group([
    //    'middleware' => 'sys-auth:jwt_web',
    'namespace' => 'Demo\Http\Request\Web',
], function (Router $router) {

    $router->any('token', 'TokenController@index')
        ->name('demo:web.token.index');
});

Route::group([
    'namespace' => 'Demo\Http\Request\Web',
], function (Router $router) {
    $router->any('content', 'ContentController@index')
        ->name('demo:web.content.index');
    $router->any('content/form', 'ContentController@form')
        ->name('demo:web.content.form');
    $router->any('form/{type}', 'FormController@index')
        ->name('demo:web.form.index');

    $router->any('table/easy', 'TableController@easy')
        ->name('demo:web.table.easy');

    /* Grid
     * ---------------------------------------- */
    $router->any('grid/more/{type?}', 'GridController@index')
        ->name('demo:web.grid.index');
    $router->any('grid/no_file', 'GridController@noFile')
        ->name('demo:web.grid.no_file');

    /* Helper 示例
     * ---------------------------------------- */
    $router->any('helper/env', 'HelperController@env')
        ->name('demo:web.helper.env');
    $router->any('helper/image', 'HelperController@image')
        ->name('demo:web.helper.image');
    $router->any('helper/tree', 'HelperController@tree')
        ->name('demo:web.helper.tree');
    $router->any('helper/img_str', 'HelperController@imgStr')
        ->name('demo:web.helper.img_str');
    $router->any('helper/img_bmp', 'HelperController@imgBmp')
        ->name('demo:web.helper.img_bmp');

    /* 邮箱
     * ---------------------------------------- */
    $router->any('mail/{slug?}/{page?}', 'MailController@index')
        ->name('demo:web.mail.index');

    /* 前端文档
     * ---------------------------------------- */
    $router->any('js', 'JsController@index')
        ->name('demo:web.js.index');

    /* Exception
     * ---------------------------------------- */
    $router->any('exception/validation_when', 'ExceptionController@validationWhen');
    $router->any('exception/validation_auto', 'ExceptionController@validationAuto');
    $router->any('exception/validation', 'ExceptionController@validation');
    $router->any('exception/{type}', 'ExceptionController@index')
        ->name('demo:web.exception.index');
});
