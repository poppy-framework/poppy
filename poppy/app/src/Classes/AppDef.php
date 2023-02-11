<?php

declare(strict_types = 1);

namespace Poppy\App\Classes;

class AppDef
{
    public static function ckItem(int $appid): string
    {
        return 'app:list:app-' . $appid;
    }
}