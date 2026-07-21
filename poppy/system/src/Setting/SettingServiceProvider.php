<?php

declare(strict_types = 1);

namespace Poppy\System\Setting;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\System\Setting\Repository\SettingRepository;

class SettingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides(): array
    {
        return ['poppy.system.setting', SettingContract::class];
    }

    /**
     * Register for service provider.
     */
    public function register(): void
    {
        $this->app->singleton('poppy.system.setting', function () {
            return new SettingRepository();
        });
        $this->app->bind(SettingContract::class, SettingRepository::class);
    }
}
