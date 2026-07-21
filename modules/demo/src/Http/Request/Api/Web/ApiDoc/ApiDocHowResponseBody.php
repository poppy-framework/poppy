<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\ApiDoc;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="DemoApiDocHowResponseBody",
 *     description="ApiDoc 编写示例响应 (回显请求参数)"
 * )
 */
class ApiDocHowResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="回显请求参数 (任意键值对)",
     *     type="object",
     *     additionalProperties=true,
     *     example={"number": 1, "string": "hello", "string_select": "apple"}
     * )
     */
    public object $data;
}
