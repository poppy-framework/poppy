<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Validation;

use Poppy\Content\Models\SysContent;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Route;

class ContentRequest extends Request
{

    protected bool $isValidate = false;

    public function attributes(): array
    {
        return [
            'type'        => '内容类型',
            'thumb'       => '缩略图',
            'keyword'     => '关键词',
            'description' => '描述',
            'cat_id'      => '分类ID',
            'content'     => '内容',
            'title'       => '标题',
            'author'      => '作者',
            'create_at'   => '创建时间',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $tbName = (new SysContent())->getTable();
        $id     = Route::input('id');
        $type   = input('type');
        return [
            'type'        => [
                Rule::string(),
            ],
            'thumb'       => [
                Rule::string(),
            ],
            'keyword'     => [
                Rule::string(),
            ],
            'description' => [
                Rule::string(),
            ],
            'cat_id'      => [
                Rule::nullable(),
            ],
            'content'     => [
                Rule::string(),
                Rule::nullable(),
            ],
            'title'       => [
                Rule::required(),
                Rule::string(),
                Rule::unique($tbName, 'title')->where(function ($query) use ($id, $type) {
                    $query->where('type', $type);
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'author'      => [
                Rule::string(),
            ],
            'create_at'   => [
                Rule::dateFormat('Y-m-d H:i:s'),
            ],
        ];
    }
}
