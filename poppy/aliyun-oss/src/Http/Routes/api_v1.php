<?php

declare(strict_types = 1);

Route::group([
    'namespace' => 'Poppy\AliyunOss\Http\Request\ApiV1\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('sts/temp_oss', 'StsController@tempOss');
});