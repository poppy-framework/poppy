<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\Validation;

use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Route;

class CategoryEstablishRequest extends Request
{
    public function rules(): array
    {
        $tableName = (new SysCategory())->getTable();
        $id        = Route::input('id');
        $type      = input('type');
        return [
            'type'      => [
                Rule::required(),
                Rule::in(array_keys(SysCategory::kvType())),
            ],
            'title'     => [
                Rule::required(),
                Rule::string(),
                Rule::unique($tableName, 'title')->where(function ($query) use ($id, $type) {
                    $query->where('type', $type);
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'parent_id' => [
                Rule::numeric(),
            ],
            'name'      => [
                Rule::string(),
                Rule::unique($tableName, 'name')->where(function ($query) use ($id, $type) {
                    $query->where('type', $type);
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
        ];
    }

    public function attributes(): array
    {
        return sys_db(SysCategory::class);
    }
}