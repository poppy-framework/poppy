<?php

declare(strict_types = 1);

namespace Poppy\System\Listeners\PamLogout;

use Poppy\System\Action\Sso;
use Poppy\System\Events\PamLogoutEvent;
use Poppy\System\Models\PamAccount;
use Throwable;

/**
 * 前台用户触发 SSO 退出
 */
class SsoListener
{
    /**
     * @throws Throwable
     */
    public function handle(PamLogoutEvent $event): void
    {
        if (PamAccount::TYPE_BACKEND === $event->pam->type) {
            return;
        }
        $token = jwt_token();
        if ($token) {
            (new Sso())->logout($event->pam->id, $token);
        }
    }
}
