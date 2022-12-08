<?php

declare(strict_types = 1);

namespace Poppy\Area\Classes;

class PyAreaDef
{
    /**
     * ID -> PID 映射
     * @return string
     */
    public static function ckMatchIdPid(): string
    {
        return 'match_id_pid';
    }


    /**
     * 地区缓存
     * @param string $suffix
     * @return string
     */
    public static function ckArea(string $suffix = ''): string
    {
        return 'area' . ($suffix ? '-' . $suffix : '');
    }

    /**
     * 国家缓存
     * @param string $suffix
     * @return string
     */
    public static function ckCountry(string $suffix = ''): string
    {
        return 'country' . ($suffix ? '-' . $suffix : '');
    }
}