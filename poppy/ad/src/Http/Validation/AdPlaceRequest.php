<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http\Validation;

use Illuminate\Support\Facades\Route;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class AdPlaceRequest extends Request
{

    protected bool $isValidate = false;

    public function attributes(): array
    {
        return [
            'title'     => '标题',
            'width'     => '宽度',
            'height'    => '高度',
            'thumb'     => '缩略图',
            'introduce' => '介绍',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $tbName = (new SysAdPlace())->getTable();
        $id     = Route::input('id');
        return [
            'title'     => [
                Rule::required(),
                Rule::string(),
                Rule::unique($tbName, 'title')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'width'     => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
            'height'    => [
                Rule::required(),
                Rule::integer(),
                Rule::min(1),
            ],
            'thumb'     => [
                Rule::string(),
                Rule::url(),
            ],
            'introduce' => [
                Rule::required(),
                Rule::string(),
            ],
        ];
    }
}
