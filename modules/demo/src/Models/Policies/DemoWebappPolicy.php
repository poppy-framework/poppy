<?php

declare(strict_types = 1);

namespace Demo\Models\Policies;

use Poppy\System\Models\PamAccount;

class DemoWebappPolicy
{
    public function create(PamAccount $pam): bool
    {
        return false;
    }
}