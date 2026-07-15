<?php

declare(strict_types = 1);

namespace Poppy\System\Http\OpenApi;

use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="PoppySystemResponseBody",
 *     description="通用响应基类 schema (所有模块共用, 字段见子类扩展)"
 * )
 */
abstract class BaseResponseBody
{

    /**
     * @OA\Property(
     *     description="状态码",
     *     type="integer",
     *     example=1
     * )
     */
    public int $status;

    /**
     * @OA\Property(
     *     description="提示信息",
     *     type="string",
     * )
     */
    public string $message;
}
