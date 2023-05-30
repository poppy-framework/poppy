<?php

declare(strict_types = 1);

namespace Poppy\Sms\Hooks\Sms;

use Poppy\Core\Services\Contracts\ServiceArray;
use Poppy\Sms\Classes\LianLuSmsProvider;
use Poppy\Sms\Http\MgrPage\FormSettingLianLu;

class SendTypeLianLu implements ServiceArray
{

    public function key(): string
    {
        return 'lianlu';
    }

    public function data(): array
    {
        return [
            'title'    => '联麓',
            'provider' => LianLuSmsProvider::class,
            'setting'  => FormSettingLianLu::class,
            'route'    => 'py-sms:backend.store.lianlu',
        ];
    }
}