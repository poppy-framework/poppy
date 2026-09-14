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
     *     description="国家列表",
     *     type="array",
     *
     *     @OA\Items(
     *         type="object",
     *
     *         @OA\Property(property="en", type="string", description="英文名"),
     *         @OA\Property(property="iso", type="string", description="国家代码 (ISO)"),
     *         @OA\Property(property="py", type="string", description="拼音首字母 (大写)"),
     *         @OA\Property(property="zh", type="string", description="中文名"),
     *         @OA\Property(property="cty", type="integer", description="国际区号")
     *     )
     * )
     */
    public object $data;
}
