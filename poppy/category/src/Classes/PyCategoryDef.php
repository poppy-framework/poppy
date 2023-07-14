<?php

declare(strict_types = 1);

namespace Poppy\Category\Classes;

class PyCategoryDef
{
    /**
     * 存储标识和 ID 的映射
     * @return string
     */
    public static function ckNameRefKey(): string
    {
        return 'name-ref-key';
    }
}