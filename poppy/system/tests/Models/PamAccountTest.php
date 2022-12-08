<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Models;

use Illuminate\Auth\AuthenticationException;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Classes\Traits\DbTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Tests\Testing\TestingPam;
use Poppy\System\Tests\Testing\TestingRole;
use Tymon\JWTAuth\JWTGuard;

class PamAccountTest extends TestCase
{
    use DbTrait;

    public function testCachedRoles()
    {
        $pam  = TestingPam::randBackend();
        $key  = 'tag:py-core-rbac:' . PyCoreDef::rbacCkUserRoles($pam->id);
        $role = TestingRole::randBackend();
        // 获取用户的缓存角色, 缓存存在值
        $pam->cachedRoles();
        $this->assertTrue(RdsDb::instance()->exists($key));
        $pam->attachRole($role);
        $pam->detachRole($role);
        $pam->attachRole($role->id);
        $pam->detachRole($role->id);
        $pam->attachRole([$role]);
        $pam->detachRole([$role]);
        $pam->attachRole([$role->id]);
        $pam->detachRole([$role->id]);
        // 缓存不存在
        $this->assertTrue(!RdsDb::instance()->exists($key));
        // 获取成功
        $pam->cachedRoles();
        // 缓存存在
        $this->assertTrue(RdsDb::instance()->exists($key));
    }


    public function testPermissions()
    {
        $pam         = TestingPam::randBackend();
        $permissions = PamAccount::permissions($pam);
        $this->assertNotNull($permissions, 'User has no permission');
        $names = $permissions->pluck('name');
        $this->assertNotNull($names, 'User has no permission');
    }

    public function testJwtToken()
    {
        $user = TestingPam::randUser();
        /** @var JWTGuard $Jwt */
        $Jwt   = auth('jwt_web');
        $token = $Jwt->tokenById($user->id);

        try {
            if ($Jwt->setToken($token)->authenticate()) {
                $this->assertTrue(true);
            }
            else {
                $this->fail('use `jwt:secret` generate token');
            }
        } catch (AuthenticationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testType()
    {
        $mail = $this->faker()->email;
        $type = PamAccount::passportType($mail);
        $this->assertEquals('email', $type);
    }

    public function testExclude()
    {
        $exclude = TestingPam::exclude();
        $this->assertNotNull($exclude);
    }
}