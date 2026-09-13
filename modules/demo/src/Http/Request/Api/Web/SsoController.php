<?php

namespace Demo\Http\Request\Api\Web;

use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\WebApiController;

class SsoController extends WebApiController
{
    public function access()
    {
        return Resp::success('通过 Sso 的登录用户的信息', [
            'id' => $this->pam->id,
        ]);
    }
}
