<?php

declare(strict_types = 1);

namespace Poppy\App\Http\Validation;

use Poppy\App\Models\SysApp;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;
use Route;

class AppEstablishRequest extends Request
{

    public function attributes(): array
    {
        return [
            'title'        => '应用名称',
            'secret'       => '应用密钥',
            'name'         => '应用名称',
            'account_type' => '账号类型',
            'account_id'   => '账号ID',
            'note'         => '备注',
            'permissions'  => '权限',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $id = Route::input('id');
        return [
            'title'        => [
                Rule::required(),
                Rule::string(),
                Rule::max(50),
            ],
            'secret'       => [
                Rule::required(),
                Rule::string(),
            ],
            'name'         => [
                Rule::string(),
                Rule::between(5, 16),
                Rule::regex('/^[a-z][a-z0-9_]{4,15}$/'),
                Rule::unique((new SysApp())->getTable(), 'name')->where(function ($query) use ($id) {
                    if ($id) {
                        $query->where('id', '!=', $id);
                    }
                }),
            ],
            'account_type' => [
                Rule::in(array_keys(PamAccount::kvType())),
            ],
            'account_id'   => [
                Rule::numeric(),
            ],
            'note'         => [
                Rule::nullable(),
            ],
            'permissions'  => [
                Rule::array(),
            ],
        ];
    }
}
