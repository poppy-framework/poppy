<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamToken;

class PamLogoutEvent
{
    /**
     * 用户 ID
     * @var int
     */
    public int $accountId;

    /**
     * 用户登录的 Token
     * @var PamToken
     */
    public PamToken $token;


    public function __construct(int $accountId, PamToken $token)
    {
        $this->accountId = $accountId;
        $this->token     = $token;
    }

}