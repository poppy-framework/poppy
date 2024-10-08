<?php

declare(strict_types = 1);

namespace Poppy\System\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Poppy\System\Models\SysConfig;
use Throwable;

class SysConfigConvertCommand extends Command
{
    protected $signature = 'py-system:config-convert';

    protected $description = 'Convert config from old to new';

    public function handle(): int
    {
        SysConfig::query()
            ->chunkById(50, function (Collection $chunks) {
                $chunks->each(function (SysConfig $config) {
                    try {
                        $value = unserialize($config->value);

                        $config->content = $value;
                        $config->save();
                    } catch (Throwable $e) {
                        $this->error(sprintf('failed: %s - %s', $config->id, $e->getMessage()));
                    }
                });
            });

        return 0;
    }
}