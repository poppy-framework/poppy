<?php

declare(strict_types = 1);

namespace Poppy\System\Listeners\AuthLogout;

use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamLog;
use Request;

/**
 * 退出系统Listener
 */
class LogoutLogListener
{
    /**
     * Handle the event.
     * @param PamAccount $user 用户账号
     * @return void
     */
    public function handle($user)
    {
        PamLog::create([
            'account_id'   => $user->id,
            'account_name' => $user->username,
            'account_type' => $user->type,
            'log_type'     => 'success',
            'log_ip'       => Request::ip(),
            'log_content'  => '登出系统',
        ]);
    }
}
