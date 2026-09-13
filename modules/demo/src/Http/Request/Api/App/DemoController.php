<?php

namespace Demo\Http\Request\Api\App;

use Poppy\Framework\Application\ApiController;
use Poppy\Framework\Classes\Resp;

class DemoController extends ApiController
{

    public function index()
    {
        return Resp::success('OK');
    }
}
