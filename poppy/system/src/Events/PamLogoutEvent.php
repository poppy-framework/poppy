<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

class PamLogoutEvent
{
    /**
     * 用户 ID
     * @var int
     */
    public int $accountId;


    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }

}