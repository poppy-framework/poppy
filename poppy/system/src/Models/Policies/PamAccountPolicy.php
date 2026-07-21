<?php

declare(strict_types = 1);

namespace Poppy\System\Models\Policies;

use Poppy\System\Classes\Traits\PolicyTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\SysConfig;

/**
 * PamAccount 策略
 */
class PamAccountPolicy
{
    use PolicyTrait;

    protected static array $permissionMap = [
        'password' => 'backend:py-system.pam.password',
    ];

    /**
     * 编辑
     *
     * @param PamAccount $pam 账号
     *
     * @return bool
     */
    public function create(PamAccount $pam)
    {
        return true;
    }

    /**
     * 编辑
     *
     * @param PamAccount $pam  账号
     * @param PamAccount $item 账号
     *
     * @return bool
     */
    public function edit(PamAccount $pam, PamAccount $item)
    {
        return true;
    }

    /**
     * 保存权限
     *
     * @param PamAccount $pam  账号
     * @param PamAccount $item 账号
     *
     * @return bool
     */
    public function enable(PamAccount $pam, PamAccount $item)
    {
        return SysConfig::NO === $item->is_enable;
    }

    public function password(PamAccount $pam, PamAccount $item): bool
    {
        return true;
    }

    /**
     * 删除
     *
     * @param PamAccount $pam  账号
     * @param PamAccount $item 账号
     *
     * @return bool
     */
    public function disable(PamAccount $pam, PamAccount $item)
    {
        // 不得禁用自身
        if ($pam->id === $item->id) {
            return false;
        }

        return !$this->enable($pam, $item);
    }

    // region 后台用户权限

    /**
     * 设置后台用户通行证
     */
    public function beMobile(PamAccount $pam, PamAccount $item): bool
    {
        return $pam->hasRole(PamRole::BE_ROOT)
            && PamAccount::TYPE_BACKEND === $item->type;
    }

    public function beClearMobile(PamAccount $pam, PamAccount $item): bool
    {
        return $pam->hasRole(PamRole::BE_ROOT)
            && PamAccount::TYPE_BACKEND === $item->type
            && 17 === strlen($item->mobile);   // 33023-{11};
    }

    // endregion
}
