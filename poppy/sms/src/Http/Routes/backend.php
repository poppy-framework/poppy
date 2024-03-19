<?php

declare(strict_types = 1);

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\Sms\Http\Request\Backend',
], function (Router $router) {
    // 短信模版配置
    $router->get('sms', 'SmsController@index')
        ->name('py-sms:backend.sms.index');
    $router->any('sms/establish/{id?}', 'SmsController@establish')
        ->name('py-sms:backend.sms.establish');
    $router->any('sms/destroy/{id}', 'SmsController@destroy')
        ->name('py-sms:backend.sms.destroy');

    $router->any('sms/store', 'SmsController@store')
        ->name('py-sms:backend.sms.store');
    $router->any('store/aliyun', 'StoreController@aliyun')
        ->name('py-sms:backend.store.aliyun');
    $router->any('store/chuanglan', 'StoreController@chuanglan')
        ->name('py-sms:backend.store.chuanglan');
    $router->any('store/lianlu', 'StoreController@lianlu')
        ->name('py-sms:backend.store.lianlu');
    $router->any('store/volc', 'StoreController@volc')
        ->name('py-sms:backend.store.volc');
});