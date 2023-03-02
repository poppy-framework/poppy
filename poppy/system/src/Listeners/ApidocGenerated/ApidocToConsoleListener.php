<?php

declare(strict_types = 1);

namespace Poppy\System\Listeners\ApidocGenerated;

use Poppy\Core\Events\ApidocGeneratedEvent;
use Poppy\System\Action\Console;

/**
 * Apidoc 上传到 Console
 */
class ApidocToConsoleListener
{
    /**
     * Handle the event.
     * @param ApidocGeneratedEvent $event 用户账号
     * @return void
     */
    public function handle(ApidocGeneratedEvent $event): void
    {
        $Sso = new Console();
        if ($event->type === 'web' && !$Sso->apidocCapture($event->type)) {
            sys_info('py-system.apidoc', $Sso->getError()->getMessage());
        }
    }
}
