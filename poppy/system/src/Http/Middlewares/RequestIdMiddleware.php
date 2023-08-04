<?php
declare(strict_types = 1);

namespace Poppy\System\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\EnvHelper;


class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = Str::uuid()->toString();

        $request->requestId = $requestId;

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}