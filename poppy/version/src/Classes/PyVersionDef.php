<?php

declare(strict_types = 1);

namespace Poppy\Version\Classes;

class PyVersionDef
{
    /**
     * 当前最大版本号缓存
     */
    public static function ckMaxVersion(): string
    {
        return 'max-version';
    }

    /**
     * 当前所有版本
     */
    public static function ckVersions(): string
    {
        return 'versions';
    }
}
