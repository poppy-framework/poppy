<?php

namespace Demo\Http\Request\Api\App;

use Poppy\Framework\Application\ApiController;
use Poppy\Framework\Classes\Resp;

/**
 * 内容生成器
 */
class DemoController extends ApiController
{

    /**
     * 主页
     */
    public function index()
    {
        return Resp::success('OK');
    }
}