<?php

declare(strict_types = 1);

namespace Poppy\Framework\Foundation\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * poppy console kernel
 */
class Kernel extends ConsoleKernel
{
    /**
     * 定义计划命令
     *
     * @param Schedule $schedule schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $this->app['events']->dispatch('console.schedule', [$schedule]);
    }
}
