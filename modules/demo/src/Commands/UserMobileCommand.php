<?php namespace Demo\Commands;

use Illuminate\Console\Command;
use Poppy\System\Models\PamAccount;

/**
 * 更新用户手机号
 */
class UserMobileCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'demo:mobile';

    /**
     * 描述
     * @var string
     */
    protected $description = 'Update user not exists mobile.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = PamAccount::whereIn('type', [PamAccount::TYPE_BACKEND, PamAccount::TYPE_DEVELOP])->pluck('id', 'username');
        if (!$user) {
            return;
        }
        collect($user)->map(function ($id) {
            PamAccount::where('id', $id)->update([
                'mobile' => '33023-' . sprintf("%s%'.07d", '', $id),
            ]);
        });
    }
}