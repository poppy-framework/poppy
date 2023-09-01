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
        return sys_db(SysContent::class);
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
            'type'      => [
                Rule::string(),
            ],
            'thumb'     => [
                Rule::string(),
            ],
            'slug'      => [
                Rule::required(),
                Rule::string(),
                Rule::regex('/^[a-z][a-z0-9-]{9,}/'),
                Rule::unique($tbName, 'slug')->where(function ($query) use ($id, $type) {
                    $query->where('type', $type);
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'cat_id'    => [
                Rule::nullable(),
            ],
            'content'   => [
                Rule::nullable(),
            ],
            'text'      => [
                Rule::string(),
            ],
            'title'     => [
                Rule::required(),
                Rule::string(),
                Rule::unique($tbName, 'title')->where(function ($query) use ($id, $type) {
                    $query->where('type', $type);
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'author'    => [
                Rule::string(),
            ],
            'create_at' => [
                Rule::dateFormat('Y-m-d H:i:s')
            ],
        ];
    }
}
