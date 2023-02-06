<?php

declare(strict_types=1);

namespace Poppy\System\Http\Middlewares;

use Closure;
use File;
use HTMLPurifier_Config;
use Illuminate\Http\Request;
use Storage;

/**
 * Html净化
 */
class HtmlPurifier
{
    public function handle(Request $request, Closure $next)
    {
        $Storage = Storage::disk('storage');
        $cachePath = $Storage->path('html_purifier/');
        if (!File::exists($cachePath)){
            File::makeDirectory($cachePath);
        }
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cachePath);
        $Purifier = new \HTMLPurifier($config);
        $input = $request->all();
        array_walk_recursive($input, static function (&$input) use ($Purifier) {
            $input = $Purifier->purify($input);
        });
        $request->merge($input);
        return $next($request);
    }
}