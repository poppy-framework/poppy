<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

/**
 * 用户注册事件
 */
class PamRegisteredEvent
{
    public PamAccount $pam;

    /**
     * PamRegisteredEvent constructor.
     */
    public function __construct(PamAccount $pam)
    {
        $this->pam = $pam;
    }
}
