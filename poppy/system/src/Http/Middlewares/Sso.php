<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Middlewares;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Poppy\Core\Redis\RdsDb;
use Poppy\System\Classes\PySystemDef;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

/**
 * 单点登录
 */
class Sso extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = jwt_token();

        try {
            if (!$token || !$payload = $this->auth->setToken($token)->check(true)) {
                return response('Unauthorized Jwt.', 401);
            }
            // 这里会抛出异常, IDE 提示不正确
        } catch (Exception $e) {
            return response('Unauthorized Jwt. Sso check token invalid', 401);
        }

        // 是否开启单点登录
        if (!\Poppy\System\Action\Sso::isEnable()) {
            return $next($request);
        }

        // sso check
        $md5Token = md5($token);
        $pamId    = data_get($payload, 'sub');

        $Rds     = RdsDb::instance();
        $devices = $Rds->hGet(PySystemDef::ckTagSsoValid(), $pamId);
        if (!$devices) {
            return response('Unauthorized Jwt, No valid device.', 401);
        }
        if (array_key_exists($md5Token, $devices)) {
            return $next($request);
        }
        return response('Unauthorized Jwt, Token unValid.', 401);
    }
}