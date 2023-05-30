<?php

declare(strict_types = 1);

namespace Poppy\Sms\Classes;

use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Sms\Action\Sms;
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
            $sendType = Sms::rateSmsType();
            $hooks    = sys_hook('poppy.sms.send_type');
            if (!$sendType) {
                $sendType = Sms::SCOPE_LOCAL;
            }
            $sender      = $hooks[$sendType];
            $senderClass = $sender['provider'] ?? LocalSmsProvider::class;
            /** @var SmsContract|AppTrait $Sms */
            self::$instance = new $senderClass();
        }
        return self::$instance;
    }
}
