<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord;

use Poppy\Framework\Exceptions\ModuleNotFoundException;
use Poppy\Framework\Support\PoppyServiceProvider;
use Poppy\SensitiveWord\Commands\InitCommand;
use Poppy\SensitiveWord\Http\RouteServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{

    /**
     * Bootstrap the module services.
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot()
    {
        parent::boot('poppy.sensitive-word');
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        $this->commands([
            InitCommand::class,
        ]);
    }
}
