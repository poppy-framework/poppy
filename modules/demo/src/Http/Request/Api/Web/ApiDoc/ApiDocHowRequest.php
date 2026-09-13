<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\ApiDoc;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;


class ApiDocHowRequest extends Request
{
    public function attributes(): array
    {
        return [
            'number'         => '数值',
            'number_range'   => '数值范围',
            'string'         => '字串',
            'string_mx'      => '字串最大5',
            'string_between' => '字串间隔',
            'number_between' => '数值间隔',
            'number_select'  => '数值间隔',
            'string_select'  => '字串枚举',
        ];
    }

    public function rules(): array
    {
        return [
            'number'         => [Rule::nullable(), Rule::integer()],
            'number_range'   => [Rule::nullable(), Rule::integer(), Rule::between(100, 999)],
            'string'         => [Rule::nullable(), Rule::string()],
            'string_mx'      => [Rule::nullable(), Rule::string(), Rule::max(5)],
            'string_between' => [Rule::nullable(), Rule::string(), Rule::between(2, 5)],
            'number_between' => [Rule::nullable(), Rule::integer(), Rule::between(2, 5)],
            'number_select'  => [Rule::nullable(), Rule::integer(), Rule::in([1, 2, 3, 99])],
            'string_select'  => [Rule::nullable(), Rule::string(), Rule::in(['banana', 'apple', 'ball'])],
        ];
    }
}
