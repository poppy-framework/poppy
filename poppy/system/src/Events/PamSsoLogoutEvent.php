<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamToken;

class PamSsoLogoutEvent
{
    /**
     * 用户 ID
     */
    public int $accountId;

    /**
     * 用户登录的 Token
     */
    public PamToken $token;

    public function __construct(int $accountId, $token)
    {
        $this->accountId = $accountId;
        $this->token     = $token;
    }
}
