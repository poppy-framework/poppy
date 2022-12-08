<?php

namespace Poppy\System\Tests\Models;

use Poppy\Framework\Application\TestCase;
use Poppy\System\Classes\Traits\DbTrait;
use Poppy\System\Models\PamPermission;
use Poppy\System\Models\PamPermissionRole;
use Poppy\System\Models\PamRole;

class PamPermissionTest extends TestCase
{
    use DbTrait;

    public function testSync()
    {
        $this->enableQueryLog();;
        $permission = PamPermission::firstOrCreate([
            'name' => 'testing:a.b.c',
        ], [
            'title' => 'testing-abc',
        ]);
        /** @var PamRole $role */
        $role = PamRole::inRandomOrder()->first();

        PamPermissionRole::create([
            'role_id'       => $role->id,
            'permission_id' => $permission->id,
        ]);

        $permission->delete();

        $this->printQueryLog();
    }
}