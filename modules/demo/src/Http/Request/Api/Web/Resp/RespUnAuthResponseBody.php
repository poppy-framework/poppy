<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DemoRespUnAuthResponseBody",
 *     description="未授权响应示例 (HTTP 401)"
 * )
 */
class RespUnAuthResponseBody
{
    /**
     * @OA\Property(description="状态码", type="integer", example=401)
     */
    public int $status;

    /**
     * @OA\Property(description="提示信息", type="string", example="Token 错误")
     */
    public string $message;
}
