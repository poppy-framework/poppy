<?php

declare(strict_types = 1);

namespace Poppy\Core\Commands;

use Illuminate\Console\Command;

/**
 * User
 */
class OpCommand extends Command
{
    /**
     * @var string 名称
     */
    protected $signature = 'py-core:op
        {do : Maintain type}
    ';

    /**
     * @var string 描述
     */
    protected $description = 'Maintain Tool.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $do = $this->argument('do');
        switch ($do) {
            case 'clear':
                sys_tag('py-core')->clear();
                $this->info(sys_gen_mk(self::class, 'Clear Core Cache'));
                break;
            default:
                $this->warn('Error type in maintain tool.');
                break;
        }
    }
}