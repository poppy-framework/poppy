<?php

declare(strict_types = 1);

namespace Poppy\System\Listeners\PamLogout;

use Poppy\System\Action\Sso;
use Poppy\System\Events\PamPasswordModifiedEvent;
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
    public function handle(PamPasswordModifiedEvent $event): void
    {
        if ($event->pam->type === PamAccount::TYPE_BACKEND) {
            return;
        }
        $token = jwt_token();
        if ($token) {
            $Sso = new Sso();
            $Sso->logout($event->pam->id, $token);
        }
    }
}
