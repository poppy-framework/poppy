<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Resp;

use Poppy\System\Http\OpenApi\BaseResponseBody;

class RespSuccessResponseBody extends BaseResponseBody
{
    public object $data;
}
