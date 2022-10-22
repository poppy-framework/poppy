<?php

namespace Poppy\System\Tests\Models;

use Illuminate\Auth\AuthenticationException;
use Poppy\System\Models\PamAccount;
use Poppy\System\Tests\Base\SystemTestCase;
use Poppy\System\Tests\Testing\TestingPam;
use Tymon\JWTAuth\JWTGuard;

class PamAccountTest extends SystemTestCase
{

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