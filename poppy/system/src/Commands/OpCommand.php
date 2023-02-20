<?php

declare(strict_types = 1);

namespace Poppy\System\Commands;

use Illuminate\Console\Command;
use Poppy\System\Action\Console;

class OpCommand extends Command
{
    protected $signature = 'py-system:op
        {action : Operation Type}
    ';

    protected $description = 'Operation for system';

    public function handle(): int
    {
        $action = $this->argument('action');
        switch ($action) {
            case 'gen-secret':
                $Console = new Console();
                if ($Console->generateSecret()) {
                    $this->info(sys_gen_mk('system.op', '生成并汇报成功'));
                }
                else {
                    $this->warn(sys_gen_mk('system.op', $Console->getError()));
                }
                break;
            case 'secret':
                $Console = new Console();
                $this->info(sys_gen_mk('system.op', '当前的密钥为:' . $Console->secret()));
                break;
            default:
                $this->warn(sys_gen_mk('system.op', '错误的 action'));
                break;
        }
        return 0;
    }
}