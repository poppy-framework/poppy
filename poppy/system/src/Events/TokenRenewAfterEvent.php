<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamToken;

/**
 * pamToken续期后续处理
 */
class TokenRenewAfterEvent
{
    public PamToken $pamToken;

    public string $oldTokenHash;

    public function __construct(PamToken $pamToken, string $old_token_hash)
    {
        $this->pamToken     = $pamToken;
        $this->oldTokenHash = $old_token_hash;
    }
}
