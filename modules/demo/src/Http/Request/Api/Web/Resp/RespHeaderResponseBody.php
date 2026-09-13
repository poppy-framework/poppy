<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use Poppy\System\Http\OpenApi\BaseResponseBody;

class RespHeaderResponseBody extends BaseResponseBody
{
    public object $data;
}
