<?php

declare(strict_types = 1);

namespace Poppy\App\Classes;

class AppDef
{
    /**
     * 条目缓存 KEY
     * @param int $appid
     * @return string
     */
    public static function ckItem(int $appid): string
    {
        return 'app:list:app-' . $appid;
    }
}