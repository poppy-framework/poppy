<?php

declare(strict_types = 1);

namespace Poppy\Content\Models\Policies;

use Poppy\Content\Models\SysContent;
use Poppy\System\Classes\Traits\PolicyTrait;
use Poppy\System\Models\PamAccount;

class SysContentPolicy
{
    use PolicyTrait;

    public static array $permissionMap = [
        'edit' => 'backend:py-content.content.manage',
    ];

    public function edit(PamAccount $pam, SysContent $content): bool
    {
    }
}
