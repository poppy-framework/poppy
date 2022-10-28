<?php

namespace DummyNamespace\Http\Request\Api;

use Poppy\Framework\Application\ApiController;
use Poppy\Framework\Classes\Resp;

class DemoController extends ApiController
{
    public function index()
    {
        return Resp::success('DummyNamespace Api Request Success');
    }
}