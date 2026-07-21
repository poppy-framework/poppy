<?php

declare(strict_types = 1);

namespace Poppy\Core\Classes\Traits;

use Poppy\Core\Module\ModuleManager;
use Poppy\Core\Rbac\Permission\PermissionManager;

trait CoreTrait
{
    /**
     * 获取核心的模块
     */
    public function coreModule(): ModuleManager
    {
        return app('poppy.core.module');
    }

    /**
     * 权限管理
     */
    public function corePermission(): PermissionManager
    {
        return app('poppy.core.permission');
    }
}
