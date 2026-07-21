<?php

declare(strict_types = 1);

Route::group([
    'namespace' => 'Poppy\Version\Http\Request\ApiV1\Web',
], function (Illuminate\Routing\Router $route) {
    $route->any('app/version', 'VersionController@version');
});
