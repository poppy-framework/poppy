<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;
use Response;
use Tymon\JWTAuth\JWTGuard;

/**
 * 用户禁用不可访问, 此中间件和 sys-auth:xx 合并
 *
 * @deprecated 4.2
 *
 * @removed    5.0
 */
class DisabledPam
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request 请求
     * @param Closure $next    后续处理
     */
    public function handle($request, Closure $next)
    {
        /** @var JWTGuard $guard */
        $guard = app('auth')->guard();
        if ($guard->check()) {
            /** @var PamAccount $user */
            $user = $guard->user();
            if (SysConfig::NO === $user->is_enable) {
                $reason = '用户被禁用, 原因 : ' . ($user->disable_reason ? ', 原因: ' . $user->disable_reason : '') . ', 解禁时间 : ' . $user->disable_end_at;
                $isJwt  = jwt_token();
                if ($isJwt) {
                    return Response::make($reason, 401);
                }

                return Resp::error($reason);
            }
        }

        return $next($request);
    }
}
