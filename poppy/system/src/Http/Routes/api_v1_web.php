<?php

declare(strict_types = 1);

/* 核心信息无需禁用, 仅需要加密鉴权即可
 * ---------------------------------------- */

use Poppy\System\Http\Request\ApiV1\AuthController;
use Poppy\System\Http\Request\ApiV1\CaptchaController;
use Poppy\System\Http\Request\ApiV1\CoreController;
use Poppy\System\Http\Request\ApiV1\UploadController;

Route::group([
    'middleware' => ['sys-app_sign'],
], function (Illuminate\Routing\Router $route) {
    $route->post('core/info', [CoreController::class, 'info']);
    $route->post('core/translate', [CoreController::class, 'translate']);
});

/* 可以对用户设备进行封禁
 * ---------------------------------------- */
Route::group([
    'middleware' => ['api-sign'],
], function (Illuminate\Routing\Router $route) {
    $route->post('auth/login', [AuthController::class, 'login'])
        ->name('py-system:pam.auth.login');
    $route->post('auth/exists', [AuthController::class, 'exists']);

    // captcha
    $route->post('captcha/verify_code', [CaptchaController::class, 'verifyCode']);
    $route->post('captcha/send', [CaptchaController::class, 'send']);

    // auth
    $route->post('auth/reset_password', [AuthController::class, 'resetPassword']);
    $route->post('auth/bind_mobile', [AuthController::class, 'bindMobile']);
});

// Jwt 合法性验证
Route::group([
    'middleware' => ['sys-jwt'],
], function (Illuminate\Routing\Router $route) {
    $route->post('upload/image', [UploadController::class, 'image'])
        ->name('py-system:api_v1.upload.image');
    $route->post('upload/file', [UploadController::class, 'file'])
        ->name('py-system:api_v1.upload.file');
});

// 单点登录
Route::group([
    'middleware' => ['api-sso'],
], function (Illuminate\Routing\Router $route) {
    $route->post('auth/access', [AuthController::class, 'access'])
        ->name('py-system:pam.auth.access');
    $route->post('auth/renew', [AuthController::class, 'renew'])
        ->name('py-system:pam.auth.renew');
    $route->post('auth/logout', [AuthController::class, 'logout'])
        ->name('py-system:pam.auth.logout');
});
