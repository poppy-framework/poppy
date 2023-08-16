<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

/**
 * Sms Helper
 */
class PySmsHelper
{
    /**
     * 模板缓存
     */
    /**
     * @param array|string $mobile
     * @return string
     */
    public static function transToAliyunMobile($mobile): string
    {
        return array_reduce((array) $mobile, function ($carry, $mobile) {
            $mobile = str_replace('-', '', $mobile);
            return $carry ? $carry . ',' . $mobile : $mobile;
        }, '');
    }
}