<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Models;

use Poppy\Core\Classes\PyCoreDef;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Action\Pam;
use Poppy\System\Classes\Traits\DbTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Tests\Testing\TestingPam;
use Poppy\System\Tests\Testing\TestingRole;
use Throwable;

class RbacTest extends TestCase
{
    use DbTrait;

    /**
     * @throws ApplicationException
     * @throws Throwable
     */
    public function testCachedRoles(): void
    {
        // 创建后台用户
        $pam           = new Pam();
        $fakerUsername = py_faker()->lexify('????????');
        if (!$pam->register('be_' . $fakerUsername, $fakerUsername, PamRole::BE_ROOT)) {
            $this->fail($pam->getError()->getMessage());
        }

        $pam  = TestingPam::randBackend();
        $key  = PyCoreDef::rbacCkUserRoles($pam->id);
        $role = TestingRole::randBackend();
        // 获取用户的缓存角色, 缓存存在值
        $pam->cachedRoles();
        $this->assertTrue(sys_tag('py-core-rbac')->exists($key));
        $pam->attachRole($role);
        $pam->detachRole($role);
        $pam->attachRole($role->id);
        $pam->detachRole($role->id);
        $pam->attachRole([$role]);
        $pam->detachRole([$role]);
        $pam->attachRole([$role->id]);
        $pam->detachRole([$role->id]);

        // 缓存不存在
        $this->assertNotTrue(sys_tag('py-core-rbac')->exists($key));

        // 缓存成功
        $pam->cachedRoles();

        // 缓存存在
        $this->assertTrue(sys_tag('py-core-rbac')->exists($key));
    }

    public function testPermissions()
    {
        $pam         = TestingPam::randBackend();
        $permissions = PamAccount::permissions($pam);
        $this->assertNotNull($permissions, 'User has no permission');
        $names = $permissions->pluck('name');
        $this->assertNotNull($names, 'User has no permission');
    }
}
