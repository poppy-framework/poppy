<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="DemoRespSuccessResponseBody",
 *     description="成功响应示例 (data 中可能携带 meta 指令: _reload / _location / _time)"
 * )
 */
class RespSuccessResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="成功数据. 视请求参数可能附加 meta 字段",
     *     type="object",
     *     @OA\Property(property="_reload", type="integer", description="前端重载标记 (传入 reload 时出现)", example=1),
     *     @OA\Property(property="_location", type="string", description="前端跳转地址 (传入 location 时出现)"),
     *     @OA\Property(property="_time", type="boolean", description="是否允许前端延时跳转", example=false),
     *     additionalProperties=true
     * )
     */
    public object $data;
}
