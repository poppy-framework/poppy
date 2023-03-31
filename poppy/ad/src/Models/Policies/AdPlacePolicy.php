<?php

declare(strict_types = 1);

namespace Poppy\Ad\Models\Policies;

use Poppy\System\Classes\Traits\PolicyTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;

/**
 * 用户角色策略
 */
class AdPlacePolicy
{
    use PolicyTrait;

    /**
     * @var array 权限映射
     */
    protected static array $permissionMap = [
        // create 操作 必须要有对应的  'backend:py-ad.place.establish' 权限
        'create' => 'backend:py-ad.place.manage',
        'edit'   => 'backend:py-ad.place.manage',
    ];

    /**
     * 编辑
     * @param PamAccount $pam 账号
     * @return bool
     */
    public function create(PamAccount $pam): bool
    {
        return true;
    }

    /**
     * 编辑
     * @param PamAccount $pam  账号
     * @param PamRole    $role 角色
     * @return bool
     */
    public function edit(PamAccount $pam, PamRole $role): bool
    {
        return true;
    }

}