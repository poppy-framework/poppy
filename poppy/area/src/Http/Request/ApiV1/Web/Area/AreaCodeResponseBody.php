<?php

declare(strict_types = 1);

namespace Poppy\Area\Http\Request\ApiV1\Web\Area;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppyAreaAreaCodeResponseBody",
 *     description="地区代码响应 (树形)"
 * )
 */
class AreaCodeResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="地区树",
     *     type="array",
     *
     *     @OA\Items(
     *         type="object",
     *
     *         @OA\Property(property="id", type="integer", description="ID"),
     *         @OA\Property(property="title", type="string", description="地区名称"),
     *         @OA\Property(property="code", type="string", description="地区编码 (左 6 位)"),
     *         @OA\Property(property="children", type="array", @OA\Items(type="object"), description="子地区")
     *     )
     * )
     */
    public object $data;
}
