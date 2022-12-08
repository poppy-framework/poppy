<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\AliyunOss\Http\Request\Backend',
], function (Router $router) {
    $router->any('upload/store', 'UploadController@store')
        ->name('py-aliyun-oss:backend.upload.store');
});