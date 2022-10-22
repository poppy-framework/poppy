<?php

namespace Poppy\CanalEs;

use Poppy\CanalEs\Commands\CreateIndexCommand;
use Poppy\CanalEs\Commands\ImportCommand;
use Poppy\CanalEs\Commands\MonitorCommand;
use Poppy\Framework\Support\PoppyServiceProvider;

class ServiceProvider extends PoppyServiceProvider
{

    public function boot()
    {
        parent::boot('poppy.canal-es');
    }

    /**
     * Register the module services.
     * @return void
     */
    public function register()
    {
        $this->commands([
            MonitorCommand::class,
            CreateIndexCommand::class,
            ImportCommand::class,
        ]);
    }
}
