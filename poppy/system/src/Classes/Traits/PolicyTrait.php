<?php

declare(strict_types = 1);

namespace Poppy\System\Classes\Traits;

use Poppy\System\Models\PamAccount;

/**
 * 策略映射
 */
trait PolicyTrait
{

    /**
     * 在 XX 条件的前置, 用于合并权限
     * @param PamAccount $pam     账号
     * @param string     $ability 能力
     * @return bool|null
     */
    public function before(PamAccount $pam, string $ability): ?bool
    {
        $permission = self::$permissionMap[$ability] ?? '';
        return $permission ? $pam->capable($permission) : null;
    }

    /**
     * 策略映射, 此策略映射的目的是为了和控制器共享定义, 但是为了解耦操作
     * 建议拆分权限定义和策略定义
     * @return mixed
     * @deprecated 4.2
     * @removed    5.0
     */
    public static function getPermissionMap(): array
    {
        return self::$permissionMap;
    }
}