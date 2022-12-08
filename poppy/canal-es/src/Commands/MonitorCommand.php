<?php

declare(strict_types = 1);

namespace Poppy\CanalEs\Commands;


use Illuminate\Console\Command;
use Poppy\CanalEs\Classes\Canal\Listener;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

class MonitorCommand extends Command
{
    protected $name = 'py-ce:monitor';

    public function handle()
    {
        $index = $this->argument('index');
        if (!$index) {
            $this->error('Not enough arguments (missing: "index")');
            return;
        }

        $output = function ($output) {
            $this->info($output);
        };

        try {
            $Listener = (new Listener($index))->setOutput($output);
            if (!$Listener->monitor()) {
                $this->error(sys_gen_mk(self::class, $Listener->getError()));
            }
        } catch (Throwable $e) {
            $this->error($e);
        }
    }


    /**
     * @return array|array[]
     */
    protected function getArguments(): array
    {
        return [
            ['index', InputArgument::REQUIRED, 'the index need to import'],
        ];
    }

}