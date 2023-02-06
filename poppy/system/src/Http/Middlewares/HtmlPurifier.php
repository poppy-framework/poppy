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
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path());
        $Purifier = new \HTMLPurifier($config);
        $input = $request->all();
        array_walk_recursive($input, static function (&$input) use ($Purifier) {
            $input = $Purifier->purify($input);
        });
        $request->merge($input);
        return $next($request);
    }
}