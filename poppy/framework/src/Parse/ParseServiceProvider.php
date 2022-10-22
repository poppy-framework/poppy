<?php

namespace Poppy\Framework\Parse;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

/**
 * ParseServiceProvider
 */
class ParseServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * Register the service provider.
     * @return void
     */
    public function register()
    {
        $this->app->singleton('poppy.yaml', function ($app) {
            return new Yaml();
        });

        $this->app->singleton('poppy.ini', function ($app) {
            return new Ini();
        });

        $this->app->singleton('poppy.xml', function ($app) {
            return new Xml();
        });
    }

    /**
     * @inheritDoc
     */
    public function provides()
    {
        return [
            'poppy.yaml',
            'poppy.ini',
            'poppy.xml',
        ];
    }
}
