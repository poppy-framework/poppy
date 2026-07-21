<?php

namespace Demo\Tests\Rbac;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Poppy\Core\Rbac\Contracts\RbacPermissionContract;
use Poppy\Core\Rbac\Contracts\RbacRoleContract;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Action\Role;

class RoleTest extends TestCase
{
    private $roleModel;

    private $permissionModel;

    public function setUp(): void
    {
        parent::setUp();
        $this->roleModel       = config('poppy.core.rbac.role');
        $this->permissionModel = config('poppy.core.rbac.permission');
    }

    public function testCreate()
    {
        /** @var Model|RbacRoleContract $role */
        $roleDb = (new $this->roleModel());
        $role   = $roleDb->create([
            'title' => 'role-' . Str::random(8),
            'name'  => 'role-' . Str::random(8),
            'type'  => 'backend',
        ]);

        /** @var Model|RbacPermissionContract $permission */
        $permissionDb = (new $this->permissionModel());

        $permission = $permissionDb->where('name', 'backend:py-core.global.manage')->first();

        $role->attachPermission($permission);
        $role->detachPermission($permission);
        $role->delete();
    }

    public function testPermission()
    {
        $Role = (new Role());
        if (!($permission = $Role->permissions(45, false))) {
            dd($Role->getError());
        }
        else {
            dd($permission);
        }
    }
}
