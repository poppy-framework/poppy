<?php

declare(strict_types = 1);

namespace Poppy\System\Models\Policies;

use Poppy\System\Classes\Traits\PolicyTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;

/**
 * 用户角色策略
 */
class PamRolePolicy
{
    use PolicyTrait;

    /**
     * @var array 权限映射
     */
    protected static array $permissionMap = [
        'edit'       => 'backend:py-system.role.manage',
        'delete'     => 'backend:py-system.role.manage',
        'create'     => 'backend:py-system.role.manage',
        'permission' => 'backend:py-system.role.permissions',
    ];

    /**
     * 编辑
     *
     * @param PamAccount $pam 账号
     */
    public function create(PamAccount $pam): bool
    {
        return true;
    }

    /**
     * 编辑
     *
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     */
    public function edit(PamAccount $pam, PamRole $role): bool
    {
        return true;
    }

    /**
     * 保存权限
     *
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     */
    public function permission(PamAccount $pam, PamRole $role): bool
    {
        return !(PamRole::BE_ROOT === $role->name);
    }

    /**
     * 删除
     *
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     */
    public function delete(PamAccount $pam, PamRole $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return true;
    }
}
