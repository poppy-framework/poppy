<?php

use Illuminate\Routing\Router;

Route::group([
    'namespace' => 'Poppy\MgrPage\Http\Request\Backend',
], function (Router $router) {
    $router->any('cp', 'HomeController@cp')
        ->name('py-mgr-page:backend.home.cp');
    $router->any('password', 'HomeController@password')
        ->name('py-mgr-page:backend.home.password');
    $router->any('clear_cache', 'HomeController@clearCache')
        ->name('py-mgr-page:backend.home.clear_cache');
    $router->any('logout', 'HomeController@logout')
        ->name('py-mgr-page:backend.home.logout');
    $router->any('setting/{path?}/{index?}', 'HomeController@setting')
        ->name('py-mgr-page:backend.home.setting');
    $router->any('easy-web/{type}', 'HomeController@easyWeb')
        ->name('py-mgr-page:backend.home.easy-web');

    $router->get('role', 'RoleController@index')
        ->name('py-mgr-page:backend.role.index');
    $router->any('role/establish/{id?}', 'RoleController@establish')
        ->name('py-mgr-page:backend.role.establish');
    $router->any('role/delete/{id?}', 'RoleController@delete')
        ->name('py-mgr-page:backend.role.delete');
    $router->any('role/menu/{id}', 'RoleController@menu')
        ->name('py-mgr-page:backend.role.menu');

    $router->get('pam', 'PamController@index')
        ->name('py-mgr-page:backend.pam.index');
    $router->any('pam/establish/{id?}', 'PamController@establish')
        ->name('py-mgr-page:backend.pam.establish');
    $router->any('pam/password/{id}', 'PamController@password')
        ->name('py-mgr-page:backend.pam.password');
    $router->any('pam/note/{id}', 'PamController@note')
        ->name('py-mgr-page:backend.pam.note');
    $router->any('pam/disable/{id}', 'PamController@disable')
        ->name('py-mgr-page:backend.pam.disable');
    $router->any('pam/enable/{id}', 'PamController@enable')
        ->name('py-mgr-page:backend.pam.enable');
    $router->any('pam/mobile/{id}', 'PamController@mobile')
        ->name('py-mgr-page:backend.pam.mobile');
    $router->any('pam/clearMobile/{id}', 'PamController@clearMobile')
        ->name('py-mgr-page:backend.pam.clear_mobile');
    $router->any('pam/log', 'PamController@log')
        ->name('py-mgr-page:backend.pam.log');
    $router->any('pam/setting_log', 'PamController@settingLog')
        ->name('py-mgr-page:backend.pam.setting_log');
    $router->any('pam/token', 'PamController@token')
        ->name('py-mgr-page:backend.pam.token');
    $router->any('pam/ban/{id}/{type}', 'PamController@ban')
        ->name('py-mgr-page:backend.pam.ban');
    $router->any('pam/delete_token/{id}', 'PamController@deleteToken')
        ->name('py-mgr-page:backend.pam.delete_token');
    $router->any('pam/setting/{id}', 'PamController@setting')
        ->name('py-mgr-page:backend.pam.setting');

    $router->any('ban', 'BanController@index')
        ->name('py-mgr-page:backend.ban.index');
    $router->any('ban/establish/{id?}', 'BanController@establish')
        ->name('py-mgr-page:backend.ban.establish');
    $router->any('ban/setting', 'BanController@setting')
        ->name('py-mgr-page:backend.ban.setting');
    $router->any('ban/status', 'BanController@status')
        ->name('py-mgr-page:backend.ban.status');
    $router->any('ban/type', 'BanController@type')
        ->name('py-mgr-page:backend.ban.type');
    $router->any('ban/delete/{id}', 'BanController@delete')
        ->name('py-mgr-page:backend.ban.delete');

    /* 发送测试邮件
     * ---------------------------------------- */
    $router->any('mail/store', 'MailController@store')
        ->name('py-mgr-page:backend.mail.store');
    $router->any('mail/test', 'MailController@test')
        ->name('py-mgr-page:backend.mail.test');

    $router->any('upload/store', 'UploadController@store')
        ->name('py-mgr-page:backend.upload.store');
});