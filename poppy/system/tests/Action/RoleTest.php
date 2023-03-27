<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Action;

use Auth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Action\Role;
use Poppy\System\Http\Validation\PamRoleRequest;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamPermission;
use Poppy\System\Tests\Testing\TestingPam;

class RoleTest extends TestCase
{

    use CoreTrait;

    /**
     * 角色添加和权限处理
     * @throws ApplicationException
     * @throws ValidationException
     * @throws AuthorizationException
     * @throws Exception
     */
    public function testEstablish(): void
    {
        $pam = TestingPam::backend();

        Auth::login($pam);

        $validated = app(PamRoleRequest::class, [(app(Request::class))->replace([
            'title' => 'role-be-' . $this->faker()->lexify(),
            'type'  => PamAccount::TYPE_BACKEND,
        ]), $this->app])->validated();

        // 一个虚拟手机号
        $Role = new Role();
        if ($Role->establish($validated)) {
            $this->assertTrue(true);
        }

        $permissions = PamPermission::where('type', PamAccount::TYPE_BACKEND)->get();
        if (!$permissions->count()) {
            $this->fail('当前权限未初始化');
        }

        $first = $permissions->shuffle()->first();

        $role = $Role->getRole();
        $role->syncPermission($permissions);

        $this->assertEquals($permissions->count(), $role->cachedPermissions()->count());

        $role->detachPermission($first);

        $this->assertEquals($permissions->count() - 1, $role->cachedPermissions()->count());

        $role->attachPermission($first);

        $this->assertTrue(sys_tag('py-core-rbac')->exists(PyCoreDef::rbacCkRolePermissions($role->id)), '存在角色权限缓存标签');

        $this->assertEquals($permissions->count(), $role->cachedPermissions()->count());

        $role->syncPermission([]);

        $this->assertEquals(0, $role->cachedPermissions()->count());

        if ($Role->delete($role->id)) {
            $this->assertTrue(true);
        }
    }
}