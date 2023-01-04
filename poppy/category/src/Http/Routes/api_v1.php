<?php
Route::group([
    'namespace' => 'Poppy\Category\Http\Request\ApiV1\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('category/sort', 'CategoryController@sort');
});