<?php

Route::group([
    'namespace' => 'Poppy\Content\Http\Request\ApiV1\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('content/lists', 'ContentController@lists');
    $route->any('content/detail', 'ContentController@detail');
});