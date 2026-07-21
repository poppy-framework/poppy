<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

class BePamLogoutEvent
{
    public int $accountId;

    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }
}
