<?php

namespace Demo\Http\Request\Web;

use Poppy\Framework\Helper\EnvHelper;
use Poppy\System\Http\Request\Web\WebController;

class EnvHelperController extends WebController
{
    /**
     * @param $type
     */
    public function index($type)
    {
        if (!in_array($type, ['ip', 'self', 'referer', 'domain', 'scheme', 'port', 'uri', 'host', 'nqUrl', 'time', 'agent', 'isProxy', 'isWindows', 'os'], true)) {
            return 'type is illegal!';
        }

        $result = EnvHelper::$type();
        var_dump($result);
    }
}