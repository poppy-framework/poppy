<?php

namespace Demo\Http\Request\Api\Web;

use Demo\Http\Request\Api\Web\ApiDoc\ApiDocHowRequest;
use Poppy\Framework\Application\ApiController;
use Poppy\Framework\Classes\Resp;

class ApiDocController extends ApiController
{
    public function how(ApiDocHowRequest $request)
    {
        return Resp::success('返回输入值', $request->all());
    }
}
