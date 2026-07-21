<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

class PamLogoutEvent
{
    /**
     * 用户
     */
    public PamAccount $pam;

    public function __construct(PamAccount $pam)
    {
        $this->pam = $pam;
    }
}
