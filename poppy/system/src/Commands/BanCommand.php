<?php

declare(strict_types = 1);

namespace Poppy\System\Commands;

use Illuminate\Console\Command;
use Poppy\System\Action\Ban;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamBan;

class BanCommand extends Command
{
    protected $signature = 'py-system:ban
        {account_type : account_type}
        {value : ip/device}
        {--note : note}
    ';

    protected $description = 'Ban user ip or device';

    public function handle()
    {
        $accountType = (string) $this->argument('account_type');
        $value       = trim((string) $this->argument('value'));
        $note        = (string) $this->option('note');

        if (!in_array($accountType, [
            PamAccount::TYPE_USER,
            PamAccount::TYPE_BACKEND,
        ], false)) {
            $this->error('accountType类型错误');
            return;
        }

        if (strlen($value) < 10) {
            $this->error('请输入正确的设备信息(IP/设备信息)');
            return;
        }

        $data = [
            'account_type' => $accountType,
            'type'         => $this->calculateType($value),
            'value'        => $value,
            'note'         => $note,
        ];

        $Ban = new Ban();
        if (!$Ban->establish($data)) {
            $this->error($Ban->getError()->getMessage());
            return;
        }

        $this->info('添加成功');
    }

    private function calculateType(string $value): string
    {
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return PamBan::TYPE_IP;
        }

        return PamBan::TYPE_DEVICE;
    }
}