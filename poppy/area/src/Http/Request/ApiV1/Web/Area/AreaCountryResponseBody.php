<?php

declare(strict_types = 1);

namespace Poppy\Area\Http\Request\ApiV1\Web\Area;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppyAreaAreaCountryResponseBody",
 *     description="国家代码响应"
 * )
 */
class AreaCountryResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="国家代码键值对 (code => 名称)",
     *     type="object",
     *     additionalProperties={"type": "string"},
     *     example={"CN": "中国", "US": "美国"}
     * )
     */
    public object $data;
}