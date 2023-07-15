<?php

declare(strict_types = 1);

namespace Poppy\Category\Hooks;

use Illuminate\Support\HtmlString;
use Poppy\Category\Models\SysCategory;
use Poppy\Core\Services\Contracts\ServiceForm;

/**
 * 选择分类
 */
class FormCategorySelect implements ServiceForm
{
    /**
     * @param array $params 参数
     * @return HtmlString|mixed
     */
    public function builder(array $params = [])
    {
        $name    = $params['name'];
        $type    = $params['type'];
        $value   = $params['value'] ?? null;
        $options = $params['options'] ?? [];

        $options += [
            'class'       => 'layui-input',
            'placeholder' => '请选择分类',
        ];
        $lists   = SysCategory::tree($type);

        return app('poppy.mgr-page.form')->select($name, $lists, $value, $options);
    }
}