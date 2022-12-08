<?php

namespace Poppy\MgrPage\Classes;


class PyMgrPageDef
{
    /**
     * 拼音的缓存KEY
     * @return string
     */
    public static function ckTagSearchPy(): string
    {
        return 'tag:py-mgr-page:search-pinyin';
    }
}