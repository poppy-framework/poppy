<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

/**
 * 短信定义
 */
class PySmsDef
{
    /**
     * 模板缓存
     * @return string
     */
    public static function ckTemplate(): string
    {
        return 'py-sms::sms.template';
    }
}