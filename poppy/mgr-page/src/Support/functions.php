<?php

declare(strict_types = 1);

use Illuminate\Support\Str;

if (!function_exists('mgr_col')) {
    /**
     * Layui Table 列参数定义
     * @param int    $width
     * @param string $fixed
     * @param string $append
     * @return string
     */
    function mgr_col(int $width = 0, string $fixed = '', string $append = ''): string
    {
        $field     = Str::random(8);
        $strWidth  = $width ? "width:{$width}," : '';
        $strFixed  = $fixed ? "fixed:'{$fixed}'," : '';
        $strAppend = $append ? ',' . trim($append, ',') : '';
        return "lay-data=\"{{$strWidth} {$strFixed} field: '{$field}' {$strAppend}}\"";
    }
}

if (!function_exists('mgr_table')) {
    /**
     * Layui Table 初始化
     * @param string $filter
     * @return string
     */
    function mgr_table(string $filter = 'default'): string
    {
        return <<<HTML
    <script>
    $(function () {
        layui.table.init('{$filter}');
    })
    </script>
HTML;

    }
}