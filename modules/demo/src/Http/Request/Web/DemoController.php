<?php

namespace Demo\Http\Request\Web;

use Demo\Classes\Layout\Demo;
use Poppy\System\Http\Request\Web\WebController;

/**
 * 内容生成器
 */
class DemoController extends WebController
{
    /**
     * Demo
     */
    public function index()
    {
        return (new Demo());
    }
}