<?php
Route::group([
    'namespace' => 'Poppy\Content\Http\Request\ApiV1\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('content/sort', 'ContentController@sort');
});