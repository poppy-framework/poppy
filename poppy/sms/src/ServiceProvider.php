<?php

declare(strict_types = 1);

namespace Poppy\Sms;

use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\Sms\Classes\Contracts\SmsContract;
use Poppy\Sms\Classes\SmsProvider;
use Poppy\Sms\Http\RouteServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{

    /**
     * Bootstrap the application events.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.sms');
    }

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        // 配置文件
        $this->mergeConfigFrom(dirname(__DIR__) . '/resources/config/sms.php', 'poppy.sms');

        $this->app->bind('poppy.sms', function () {
            return new SmsProvider;
        });

        $this->app->alias('poppy.sms', SmsContract::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [
            'poppy.sms',
        ];
    }
}
