<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\SensitiveWord\Http\Request\Backend',
], function (Router $router) {
    $router->any('/', 'WordController@index')
        ->name('py-sensitive-word:backend.word.index');
    $router->any('establish/{id?}', 'WordController@establish')
        ->name('py-sensitive-word:backend.word.establish');
    $router->any('delete/{id?}', 'WordController@delete')
        ->name('py-sensitive-word:backend.word.delete');
});
