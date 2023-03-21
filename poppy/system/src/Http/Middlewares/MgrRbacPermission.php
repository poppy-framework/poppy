<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Middlewares;

use Poppy\Core\Rbac\Middlewares\RbacPermission;
use Poppy\Core\Rbac\Middlewares\RbacPermission as CoreRbacPermission;
use Poppy\Core\Rbac\Traits\RbacUserTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;

/**
 * RBAC 权限限定, 使用 标准 rbac, 不对超级管理员做特殊处理
 * @see        RbacPermission
 * @deprecated 4.2
 * @removed    5.0
 */
class MgrRbacPermission extends CoreRbacPermission
{
    /**
     * Handle an incoming request.
     * @param PamAccount|RbacUserTrait $user
     * @return bool
     */
    public function passed($user): bool
    {
        return $user->type === PamAccount::TYPE_BACKEND && $user->hasRole(PamRole::BE_ROOT);
    }
}