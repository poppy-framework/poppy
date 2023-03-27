<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\AliyunOss\Http\Request\ApiMgrApp',
], function (Router $router) {
    $router->any('upload/store', 'UploadController@store')
        ->name('py-aliyun-oss:api-backend.home.store');
});