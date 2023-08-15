<?php

namespace Poppy\System\Tests\Models;

use Exception;
use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Action\Role;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamPermission;
use Poppy\System\Models\PamPermissionRole;

class PamPermissionTest extends TestCase
{

    /**
     * @throws ApplicationException
     * @throws Exception
     */
    public function testSync(): void
    {
        // 创建权限
        $permission = PamPermission::firstOrCreate([
            'name' => 'testing:a.b.c',
        ], [
            'title' => 'testing-sync-' . $this->faker()->bothify('???###'),
        ]);

        $Role = new Role();
        if ($Role->establish([
            'title' => $this->faker()->lexify('testing-back-sync-????'),
            'type'  => PamAccount::TYPE_BACKEND,
        ])) {
            $this->assertTrue(true);
        }
        $role = $Role->getRole();

        PamPermissionRole::create([
            'role_id'       => $role->id,
            'permission_id' => $permission->id,
        ]);

        $permission->delete();
        $role->delete();
        $this->assertCount(0, PamPermissionRole::where('role_id', $role->id)->get());
    }
}