<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="DemoRespSuccessRequest",
 *     description="成功响应示例请求 (演示 meta 行为: location 跳转 / reload 重载)",
 *
 *     @OA\Property(property="location", type="string", nullable=true, description="跳转地址, 传入后响应携带 _location meta 触发前端跳转", example="/dashboard"),
 *     @OA\Property(property="reload", type="string", nullable=true, description="是否触发前端重载 (任意真值都生效)", enum={"1", "true", "Y"}),
 * )
 */
class RespSuccessRequest extends Request
{
    public function getLocation(): string
    {
        return (string) $this->input('location', '');
    }

    public function getReload(): string
    {
        return (string) $this->input('reload', '');
    }

    public function attributes(): array
    {
        return [
            'location' => '跳转地址',
            'reload'   => '是否重载',
        ];
    }

    public function rules(): array
    {
        return [
            'location' => [Rule::nullable(), Rule::string()],
            'reload'   => [Rule::nullable(), Rule::string()],
        ];
    }
}
