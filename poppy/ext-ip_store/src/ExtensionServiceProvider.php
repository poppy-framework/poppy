<?php

declare(strict_types = 1);

namespace Poppy\Extension\IpStore;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Poppy\Extension\IpStore\Classes\Contracts\IpContract;

/*
|--------------------------------------------------------------------------
| IP 转换接口
|--------------------------------------------------------------------------
| qqwry   :  http://www.cz88.net/
| mon17   :  https://www.ipip.net/
*/

class ExtensionServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->registerIp();
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['poppy.ext.ip_store', IpContract::class];
    }

    private function registerIp()
    {
        $store = strtolower(config('poppy.ext-ip_store.type', 'mon17'));
        $types = ['mon17', 'qqwry'];
        if (!in_array($store, $types)) {
            $store = 'mon17';
        }
        $className = __NAMESPACE__ . '\\Repositories\\' . ucfirst($store);
        $this->app->singleton('poppy.ext.ip_store', function () use ($className) {
            return new $className();
        });
        $this->app->bind(IpContract::class, 'poppy.ext.ip_store');
    }
}
