<?php

declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\PamRole;

/**
 * 角色权限更新
 */
class RolePermissionUpdatedEvent
{
    /**
     * @var PamRole
     */
    public $role;

    public function __construct(PamRole $role)
    {
        $this->role = $role;
    }
}