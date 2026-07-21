<?php

declare(strict_types = 1);

namespace Poppy\App\Classes;

class AppDef
{
    /**
     * 条目缓存 KEY
     */
    public static function ckItem(int $appid): string
    {
        return 'items:app-' . $appid;
    }
}
