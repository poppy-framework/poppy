<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Sso;

use Poppy\System\Http\OpenApi\BaseResponseBody;

class SsoAccessResponseBody extends BaseResponseBody
{
    public object $data;
}
