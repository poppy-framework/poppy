<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\App\Demo;

use Poppy\System\Http\OpenApi\BaseResponseBody;

class DemoIndexResponseBody extends BaseResponseBody
{
    // 无自定义 data 字段, 沿用 BaseResponseBody 的 code/message 即可
}
