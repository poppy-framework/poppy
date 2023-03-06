<?php

declare(strict_types = 1);

namespace Poppy\System\Models\Policies;

use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\SysConfig;

/**
 * PamAccount 策略
 */
class PamAccountPolicy
{

    /**
     * 编辑
     * @param PamAccount $pam 账号
     * @return bool
     */
    public function create(PamAccount $pam)
    {
        return true;
    }

    /**
     * 编辑
     * @param PamAccount $pam  账号
     * @param PamAccount $item 账号
     * @return bool
     */
    public function edit(PamAccount $pam, PamAccount $item)
    {
        return true;
    }

    /**
     * 保存权限
     * @param PamAccount $pam  账号
     * @param PamAccount $item 账号
     * @return bool
     */
    public function enable(PamAccount $pam, PamAccount $item)
    {
        return $item->is_enable === SysConfig::NO;
    }


    /**
     * 设置手机号
     * @param PamAccount $pam
     * @param PamAccount $item
     * @return bool
     */
    public function mobile(PamAccount $pam, PamAccount $item)
    {
        return $pam->hasRole(PamRole::BE_ROOT);
    }

    /**
     * 删除
     * @param PamAccount $pam  账号
     * @param PamAccount $item 账号
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
}