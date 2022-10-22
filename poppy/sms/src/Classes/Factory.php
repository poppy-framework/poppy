<?php

namespace Poppy\Sms\Classes;

use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Sms\Classes\Contracts\SmsContract;
use SimpleXMLElement;

class Factory
{

    /**
     * @var SmsContract|null
     */
    private static ?SmsContract $instance = null;

    /**
     * @return mixed|SimpleXMLElement
     */
    public static function instance(): BaseSms
    {
        if (!self::$instance) {
            $sendType = sys_setting('py-sms::sms.send_type');
            $hooks    = sys_hook('poppy.sms.send_type');
            if (!$sendType) {
                $sendType = 'local';
            }
            $sender      = $hooks[$sendType];
            $senderClass = $sender['provider'] ?? LocalSmsProvider::class;
            /** @var SmsContract|AppTrait $Sms */
            self::$instance = new $senderClass();
        }
        return self::$instance;
    }
}
