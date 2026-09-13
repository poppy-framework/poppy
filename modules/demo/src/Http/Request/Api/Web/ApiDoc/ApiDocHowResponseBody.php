<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\ApiDoc;

use Poppy\System\Http\OpenApi\BaseResponseBody;

class ApiDocHowResponseBody extends BaseResponseBody
{
    public object $data;
}
