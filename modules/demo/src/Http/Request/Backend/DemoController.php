<?php

namespace Demo\Http\Request\Backend;

use Poppy\MgrPage\Http\Request\Backend\BackendController;

class DemoController extends BackendController
{
    public function index()
    {
        return 'Demo Backend Request Success';
    }
}