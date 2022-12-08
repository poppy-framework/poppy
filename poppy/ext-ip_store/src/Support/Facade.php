<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore\Support;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

class Facade extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return 'poppy.ext.ip_store';
    }
}