<?php

declare(strict_types = 1);

namespace Poppy\System\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * 设备相关请求头 (跨接口共用)
 *
 * @OA\Parameter(
 *     parameter="PoppySystemXOsHeader",
 *     name="x-os",
 *     in="header",
 *     description="OS 平台类型 (可选类型 ios, android, harmony_next, pc, h5, webapp)",
 *
 *     @OA\Schema(type="string", example="pc")
 * )
 *
 * @OA\Parameter(
 *     parameter="PoppySystemXTypeHeader",
 *     name="x-type",
 *     in="header",
 *     description="账号类型 (例如 backend, user)",
 *
 *     @OA\Schema(type="string", example="user")
 * )
 *
 * @OA\Parameter(
 *     parameter="PoppySystemXIdHeader",
 *     name="x-id",
 *     in="header",
 *     description="设备 ID",
 *
 *     @OA\Schema(type="string", example="123456")
 * )
 */
abstract class DefaultDeviceHeaders
{
}
