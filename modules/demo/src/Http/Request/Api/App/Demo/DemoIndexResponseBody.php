<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\App\Demo;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="DemoAppDemoIndexResponseBody",
 *     description="Demo App index 接口响应 (无 data, 仅 code/message)"
 * )
 */
class DemoIndexResponseBody extends BaseResponseBody
{
    // 无自定义 data 字段, 沿用 BaseResponseBody 的 code/message 即可
}
