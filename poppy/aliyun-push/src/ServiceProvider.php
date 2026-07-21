<?php

declare(strict_types = 1);

namespace Poppy\AliyunPush;

use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     *
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.aliyun-push');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // 配置文件
        $this->mergeConfigFrom(dirname(__DIR__) . '/resources/config/aliyun-push.php', 'poppy.aliyun-push');
    }
}
