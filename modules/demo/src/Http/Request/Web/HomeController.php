<?php

namespace Demo\Http\Request\Web;

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
}