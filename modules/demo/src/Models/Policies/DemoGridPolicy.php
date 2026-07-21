<?php

declare(strict_types = 1);

namespace Demo\Models\Policies;

use Poppy\System\Models\PamAccount;

class DemoGridPolicy
{
    public function create(PamAccount $pam): bool
    {
        return false;
    }
}
