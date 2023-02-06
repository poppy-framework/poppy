<?php

declare(strict_types=1);

namespace Poppy\System\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

/**
 * Html净化
 */
class HtmlPurifier
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        array_walk_recursive($input, static function (&$input) {
            $input = (new \HTMLPurifier())->purify($input);
        });
        $request->merge($input);
        return $next($request);
    }
}