<?php

declare(strict_types = 1);

Route::group([
    'namespace' => 'Poppy\Area\Http\Request\ApiV1\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('area/code', 'AreaController@code');
    $route->any('area/country', 'AreaController@country');
});