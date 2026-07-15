<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\ApiDoc;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="DemoApiDocHowRequest",
 *     description="ApiDoc 编写示例请求 (演示 @OA\Property 类型与范围)",
 *     @OA\Property(property="number", type="integer", nullable=true, description="数值", example=1),
 *     @OA\Property(property="number_range", type="integer", nullable=true, description="数值范围", minimum=100, maximum=999, example=200),
 *     @OA\Property(property="string", type="string", nullable=true, description="字串", example="hello"),
 *     @OA\Property(property="string_mx", type="string", nullable=true, description="字串最大 5", maxLength=5, example="abc"),
 *     @OA\Property(property="string_between", type="string", nullable=true, description="字串长度 2~5", minLength=2, maxLength=5, example="abc"),
 *     @OA\Property(property="number_between", type="integer", nullable=true, description="数值范围 2~5", minimum=2, maximum=5, example=3),
 *     @OA\Property(property="number_select", type="integer", nullable=true, description="数值枚举", enum={1, 2, 3, 99}, example=1),
 *     @OA\Property(property="string_select", type="string", nullable=true, description="字串枚举", enum={"banana", "apple", "ball"}, example="apple"),
 * )
 */
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