<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="DemoRespHeaderResponseBody",
 *     description="请求头回显响应示例"
 * )
 */
class RespHeaderResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="回显的请求头键值对",
     *     type="object",
     *     @OA\Property(property="x-app-id", type="string", description="应用 ID"),
     *     @OA\Property(property="x-app-os", type="string", description="OS 类型", example="android"),
     *     @OA\Property(property="x-app-version", type="string", description="应用版本", example="1.0.0"),
     *     additionalProperties=true
     * )
     */
    public object $data;
}