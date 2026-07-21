<?php

declare(strict_types = 1);

namespace DummyNamespace\Http\Request\Backend;

use Poppy\MgrPage\Http\Request\Backend\BackendController;

class DemoController extends BackendController
{
    public function index(): string
    {
        return 'DummyNamespace Backend Request Success';
    }
}
