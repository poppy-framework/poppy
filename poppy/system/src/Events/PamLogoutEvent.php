<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Illuminate\Support\Collection;

class PamLogoutEvent
{
    public int $accountId;

    public Collection $tokens;

    /**
     * @param int        $accountId
     * @param Collection $tokens
     */
    public function __construct(int $accountId, Collection $tokens)
    {
        $this->accountId = $accountId;
        $this->tokens    = $tokens;
    }

}