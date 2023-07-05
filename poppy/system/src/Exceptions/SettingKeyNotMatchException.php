<?php

declare(strict_types = 1);

namespace Poppy\System\Exceptions;

use Poppy\Framework\Exceptions\BaseException;

class SettingKeyNotMatchException extends BaseException
{
    public function __construct($key = '')
    {
        $message = trans('py-system::util.exception.setting_key_not_match', [
            'key' => $key,
        ]);
        parent::__construct($message);
    }
}