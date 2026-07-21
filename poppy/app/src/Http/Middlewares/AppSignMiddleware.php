<?php

declare(strict_types = 1);

namespace Poppy\App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Poppy\App\Classes\Sign\DefaultAppSign;
use Poppy\Framework\Classes\Resp;

/**
 * 应用验签
 */
class AppSignMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request 请求
     * @param Closure $next    后续处理
     */
    public function handle(Request $request, Closure $next)
    {
        // 未启用加密, 直接过滤掉
        $Sign = new DefaultAppSign();
        if (!$Sign->check($request->all())) {
            return Resp::error($Sign->getError());
        }

        return $next($request);
    }
}
