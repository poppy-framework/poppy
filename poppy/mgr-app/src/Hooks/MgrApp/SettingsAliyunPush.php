<?php

declare(strict_types=1);

namespace Poppy\MgrApp\Hooks\MgrApp;

use Poppy\Core\Services\Contracts\ServiceArray;
use Poppy\MgrApp\Http\MgrApp\SettingAliyunPush;

class SettingsAliyunPush implements ServiceArray
{
    public function key(): string
    {
        return 'poppy.aliyun-push';
    }

    public function data(): array
    {
        return [
            'title' => '阿里云推送',
            'forms' => [
                SettingAliyunPush::class,
            ],
        ];
    }
}