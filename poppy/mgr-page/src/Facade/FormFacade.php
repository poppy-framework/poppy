<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Facade;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * 前台框架
 */
class FormFacade extends IlluminateFacade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'poppy.mgr-page.form';
    }
}
