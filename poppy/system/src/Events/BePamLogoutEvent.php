<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamAccount;

class BePamLogoutEvent
{
    public int $accountId;

    /**
     * @param int $accountId
     */
    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }


}