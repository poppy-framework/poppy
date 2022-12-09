<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Models;

use Illuminate\Auth\AuthenticationException;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Classes\Traits\DbTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Tests\Testing\TestingPam;
use Tymon\JWTAuth\JWTGuard;

class PamAccountTest extends TestCase
{
    use DbTrait;

    public function testJwtToken()
    {
        // assert user
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