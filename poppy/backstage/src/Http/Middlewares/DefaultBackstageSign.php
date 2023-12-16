<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\AppTrait;

/**
 * 默认后台加密
 */
class DefaultBackstageSign
{
    use AppTrait;

    /**
     * Handle an incoming request.
     * @param Request $request 请求
     * @param Closure $next 后续处理
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $Sign = app('poppy.backstage.sign');

        if (!$Sign->check($request)) {
            $error = $Sign->getError();
            return Resp::web($error->getCode(), $error->getMessage());
        }
        return $next($request);
    }
}