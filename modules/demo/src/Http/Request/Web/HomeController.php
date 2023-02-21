<?php

namespace Demo\Http\Request\Web;

use Demo\Classes\Layout\Demo;
use Poppy\System\Http\Request\Web\WebController;

/**
 * 内容生成器
 */
class HomeController extends WebController
{
    /**
     * Demo
     */
    public function index()
    {
        return view('demo::web.home.index');
    }

    /**
     * Demo
     */
    public function demo(): Demo
    {
        return (new Demo())
            ->title('标题')
            ->description('描述');
    }
}