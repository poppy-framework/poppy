<?php

declare(strict_types = 1);

namespace Poppy\Extension\App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use JsonException;
use Poppy\Extension\App\Classes\Sign\JsonAppSign;
use Poppy\Framework\Classes\Resp;

/**
 * 默认后台加密
 */
class JsonAppSignMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request 请求
     * @param Closure $next    后续处理
     *
     * @throws JsonException
     */
    public function handle(Request $request, Closure $next)
    {
        $Sign = new JsonAppSign();
        if (!$Sign->check($request->all())) {
            $error = $Sign->getError();

            return Resp::web($error->getCode(), $error->getMessage());
        }

        return $next($request);
    }
}
