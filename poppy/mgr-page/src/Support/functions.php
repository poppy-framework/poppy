<?php

declare(strict_types = 1);

use Illuminate\Support\Str;
use Poppy\MgrPage\Classes\Operations;

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
        $arrData = [];
        if (!Str::contains($append, 'field')) {
            $field     = Str::random(8);
            $arrData[] = "field:'{$field}'";
            $arrData[] = $append;
        }
        else {
            $arrData[] = trim($append, ',');
        }

        if ($width) {
            $arrData[] = "width:{$width}";
        }
        if ($fixed) {
            $arrData[] = "fixed:'{$fixed}'";
        }
        $strData = implode(', ', array_filter($arrData, fn($item) => trim($item)));
        return "lay-data=\"{{$strData}}\"";
    }
}


if (!function_exists('mgr_col_actions')) {
    /**
     * Layui Table 列参数定义
     * @param int    $width
     * @param string $fixed
     * @param string $append
     * @return string
     */
    function mgr_col_actions(int $width = 0, string $fixed = 'right', string $append = ''): string
    {
        return mgr_col($width, $fixed, "field: '_actions_'" . ($append ? ',' . trim($append, ',') : ''));
    }
}


if (!function_exists('mgr_table_open')) {
    /**
     * Layui Table 初始化 KEY
     * @param string $filter
     * @return string
     */
    function mgr_table_open(string $filter = 'default'): string
    {
        return <<<HTML
lay-filter="{$filter}"
HTML;
    }
}


if (!function_exists('mgr_table_close')) {
    /**
     * Layui Table 初始化 End
     * @param string $filter
     * @return string
     * @throws JsonException
     */
    function mgr_table_close(string $filter = 'default', $options = []): string
    {

        $json = json_encode($options, JSON_THROW_ON_ERROR);
        return <<<HTML
    <script>
    $(function () {
        layui.table.init('{$filter}', {$json});
    })
    </script>
HTML;
    }
}

if (!function_exists('mgr_actions')) {
    /**
     * 封装操作函数
     * @param Closure $closure
     * @return string
     */
    function mgr_actions(Closure $closure): string
    {
        $operations = new Operations();
        $closure($operations);
        return $operations->render();
    }
}

if (!function_exists('mgr_menu_title')) {
    /**
     * 菜单标题
     * @param array $link
     * @return string
     */
    function mgr_menu_title(array $link): string
    {
        $target = $link['target'] ?? '';
        $url    = $link['url'];

        $lk   = $target ? " href=\"{$url}\" target=\"{$target}\" " : "ew-href=\"{$url}\"";
        $icon = isset($link['icon']) && $link['icon'] ? '<i class="' . $link['icon'] . '"></i>' : '';
        return <<<LINK
<a {$lk}>
    {$icon}
    {$link['title']}
</a>
LINK;
    }
}


if (!function_exists('mgr_op')) {
    /**
     * 操作
     * @return Operations
     */
    function mgr_op(): Operations
    {
        return new Operations();
    }
}


if (!function_exists('mgr_dropdown')) {
    /**
     * 下拉菜单
     * @param         $title
     * @param Closure $closure
     * @return string
     */
    function mgr_dropdown($title, Closure $closure): string
    {
        $operations = new Operations();
        $operations->dropdown($title, function (Operations $ops) use ($closure) {
            $closure($ops);
        });
        return $operations->render();
    }
}