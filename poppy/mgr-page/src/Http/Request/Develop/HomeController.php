<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

/**
 * 开发平台控制台 cp = ControlPanel
 */
class HomeController extends DevelopController
{
    /**
     * 开发者控制台
     */
    public function index()
    {
        return view('py-mgr-page::develop.home.cp');
    }
}
