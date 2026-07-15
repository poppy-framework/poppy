<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Core;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemCoreInfoResponseBody",
 *     description="系统信息响应 (字段由 hook poppy.system.api_info 注入, 动态扩展)"
 * )
 */
class CoreInfoResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="系统配置信息. 字段由 hook `poppy.system.api_info` 注入, 实际键值由宿主应用决定.",
     *     type="object",
     *     additionalProperties=true,
     *     example={
     *         "version": "4.x",
     *         "site_name": "Poppy"
     *     }
     * )
     */
    public object $data;
}