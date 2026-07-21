<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http\Validation;

use Illuminate\Support\Facades\Route;
use Poppy\Ad\Models\SysAdContent;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\SysConfig;

class AdContentRequest extends Request
{
    protected bool $isValidate = false;

    public function attributes(): array
    {
        return [
            'place_id'   => '广告位',
            'title'      => '标题',
            'introduce'  => '介绍',
            'start_at'   => '开始时间',
            'end_at'     => '结束时间',
            'src'        => '图片',
            'action'     => '操作',
            'value'      => '值',
            'is_enable'  => '是否开启',
            'list_order' => '排序',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $tbName = (new SysAdContent())->getTable();
        $id     = Route::input('id');

        return [
            'place_id'   => [
                Rule::required(),
                Rule::integer(),
            ],
            'title'      => [
                Rule::required(),
                Rule::string(),
                Rule::unique($tbName, 'title')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'introduce'  => [
                Rule::required(),
                Rule::string(),
            ],
            'start_at'   => [
                Rule::required(),
                Rule::string(),
            ],
            'end_at'     => [
                Rule::required(),
                Rule::string(),
            ],
            'src'        => [
                Rule::url(),
            ],
            'action'     => [
                Rule::required(),
                Rule::string(),
            ],
            'value'      => [
                Rule::string(),
            ],
            'is_enable'  => [
                Rule::integer(),
                Rule::in(array_keys(SysConfig::kvYn())),
            ],
            'list_order' => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
        ];
    }
}
