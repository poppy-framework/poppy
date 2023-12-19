<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

class SettingRequest extends Request
{
    protected bool $isValidate = false;

    private array $settingKeys = [
        'py-system::site' => [
            'name'
        ],
    ];

    /**
     * 返回定义的类型
     * @return array|array[]
     */
    public function definedKeys(): array
    {
        return $this->settingKeys;
    }

    public function getNamespace()
    {
        return $this->input('namespace');
    }

    public function getData()
    {
        return $this->input('data');
    }

    public function scenes(): array
    {
        return [
            'namespace'       => [
                'namespace',
            ],
            'py-system::site' => [
                'namespace', 'data.name',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'namespace' => '命名空间',
            'data.name' => '网站名称'
        ];
    }

    public function rules(): array
    {
        return [
            'namespace' => [
                Rule::string(),
                Rule::required(),
                Rule::max(30),
                Rule::in(array_keys($this->settingKeys))
            ],
            'data.name' => [
                Rule::string(),
                Rule::required(),
                Rule::max(85),
            ]
        ];
    }
}
