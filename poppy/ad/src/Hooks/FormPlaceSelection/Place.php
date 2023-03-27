<?php

declare(strict_types = 1);

namespace Poppy\Ad\Hooks\FormPlaceSelection;

use Illuminate\Support\HtmlString;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Core\Services\Contracts\ServiceForm;

/**
 * 选择广告位
 */
class Place implements ServiceForm
{
    /**
     * @param array $params 参数
     * @return HtmlString|mixed
     */
    public function builder(array $params = [])
    {
        $name    = $params['name'];
        $value   = $params['value'] ?? null;
        $options = $params['options'] ?? [];

        $options += [
            'class'       => 'layui-input',
            'placeholder' => '请选择占位',
        ];
        $places  = SysAdPlace::pluck('title', 'id');

        return app('poppy.mgr-page.form')->select($name, $places, $value, $options);
    }
}