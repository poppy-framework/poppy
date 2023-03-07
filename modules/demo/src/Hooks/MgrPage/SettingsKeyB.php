<?php

namespace Demo\Hooks\MgrPage;

use Demo\Http\Forms\FormSettingAli;
use Demo\Http\Forms\FormSettingAvatar;
use Poppy\Core\Services\Contracts\ServiceArray;

class SettingsKeyB implements ServiceArray
{

    public function key(): string
    {
        return 'demo.key-b';
    }

    public function data(): array
    {
        return [
            'title' => 'KEY-B',
            'forms' => [
                FormSettingAvatar::class,
                FormSettingAli::class,
            ],
        ];
    }
}