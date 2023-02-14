<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Action;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use JWTAuth;
use Poppy\Framework\Application\TestCase;
use Poppy\System\Action\Sso;
use Poppy\System\Tests\Testing\TestingPam;

/**
 * 单点登录测试
 */
class SsoTest extends TestCase
{
    /**
     * 测试同时登录限制
     * @return void
     * @throws Exception
     * @throws GuzzleException
     */
    public function testAll(): void
    {
        $oldSsoType      = (string) sys_setting('py-system::pam.sso_type');
        $oldMaxDeviceNum = (int) (sys_setting('py-system::pam.sso_device_num') ?: 10);

        app('poppy.system.setting')->set('py-system::pam.sso_type', Sso::SSO_ALL);
        app('poppy.system.setting')->set('py-system::pam.sso_device_num', 3);

        $num  = 1;
        $Sso  = new Sso();
        $user = TestingPam::randUser();
        $Sso->banUser($user->id);

        $expired = '';
        $success = '';
        while ($num < 5) {
            $jwt = JWTAuth::fromUser($user);
            if (!$expired) {
                $expired = $jwt;
            }
            else {
                $success = $jwt;
            }
            $deviceId = 'test-sso-all-' . $num;
            if (!$Sso->handle($user, $deviceId, 'android', $jwt)) {
                $this->fail((string) $Sso->getError());
            }
            $num++;
        }

        try {
            $this->postAuth($expired);
            $this->fail('这里应该返回 401 错误, 不应该正确返回数据');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getCode());
        }

        $this->postAuth($success);
        $this->assertTrue(true, '这里应该正常请求');

        app('poppy.system.setting')->set('py-system::pam.sso_type', $oldSsoType);
        app('poppy.system.setting')->set('py-system::pam.sso_device_num', $oldMaxDeviceNum);

        $Sso->banUser($user->id);
    }


    /**
     * 测试设备登录限制
     * @return void
     * @throws Exception
     * @throws GuzzleException
     */
    public function testDevice(): void
    {
        $oldSsoType = (string) sys_setting('py-system::pam.sso_type');

        app('poppy.system.setting')->set('py-system::pam.sso_type', Sso::SSO_DEVICE);

        $num  = 1;
        $Sso  = new Sso();
        $user = TestingPam::randUser();
        $Sso->banUser($user->id);

        $expired = '';
        $success = '';
        while ($num <= 4) {
            $jwt = JWTAuth::fromUser($user);
            if (!$expired) {
                $expired = $jwt;
            }
            else {
                $success = $jwt;
            }
            $deviceId = 'test-sso-device-' . $num;
            if (!$Sso->handle($user, $deviceId, 'android', $jwt)) {
                $this->fail((string) $Sso->getError());
            }
            $num++;
        }

        try {
            $this->postAuth($expired);
            $this->fail('这里应该返回 401 错误, 不应该正确返回数据');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getCode());
        }

        $this->postAuth($success);
        $this->assertTrue(true, '这里应该正常请求');

        app('poppy.system.setting')->set('py-system::pam.sso_type', $oldSsoType);

        $Sso->banUser($user->id);
    }

    /**
     * 测试单设备登录
     * @return void
     * @throws Exception
     * @throws GuzzleException
     */
    public function testSingle(): void
    {
        $oldSsoType = (string) sys_setting('py-system::pam.sso_type');

        app('poppy.system.setting')->set('py-system::pam.sso_type', Sso::SSO_SINGLE);

        $num  = 1;
        $Sso  = new Sso();
        $user = TestingPam::randUser();
        $Sso->banUser($user->id);

        $expired = '';
        $success = '';
        while ($num <= 6) {
            $jwt = JWTAuth::fromUser($user);
            if (!$expired) {
                $expired = $jwt;
            }
            else {
                $success = $jwt;
            }
            $deviceId   = 'test-sso-single-' . $num;
            $deviceType = py_faker()->randomElement(['android', 'ios']);
            if (!$Sso->handle($user, $deviceId, $deviceType, $jwt)) {
                $this->fail((string) $Sso->getError());
            }
            $num++;
        }

        try {
            $this->postAuth($expired);
            $this->fail('这里应该返回 401 错误, 不应该正确返回数据');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getCode());
        }

        $this->postAuth($success);
        $this->assertTrue(true, '这里应该正常请求');

        app('poppy.system.setting')->set('py-system::pam.sso_type', $oldSsoType);

        $Sso->banUser($user->id);
    }


    /**
     * 测试单设备登录
     * @return void
     * @throws Exception
     * @throws GuzzleException
     */
    public function testGroup(): void
    {
        $oldSsoType = (string) sys_setting('py-system::pam.sso_type');

        app('poppy.system.setting')->set('py-system::pam.sso_type', Sso::SSO_GROUP);


        $Sso  = new Sso();
        $user = TestingPam::randUser();
        $Sso->banUser($user->id);

        $expired1 = [];
        $success1 = '';
        $num      = 1;
        while ($num <= 6) {
            $jwt = JWTAuth::fromUser($user);
            if ($num !== 6) {
                $expired1[] = $jwt;
            }
            else {
                $success1 = $jwt;
            }

            $deviceType = py_faker()->randomElement(['android', 'ios']);
            $deviceId   = 'test-sso-group-' . $num . '-' . $deviceType;
            $this->outputVariables($deviceId);
            if (!$Sso->handle($user, $deviceId, $deviceType, $jwt)) {
                $this->fail((string) $Sso->getError());
            }
            $num++;
        }

        $expired2 = [];
        $success2 = '';
        $num      = 1;
        while ($num <= 6) {
            $jwt = JWTAuth::fromUser($user);
            if ($num !== 6) {
                $expired2[] = $jwt;
            }
            else {
                $success2 = $jwt;
            }

            $deviceType = py_faker()->randomElement(['mac', 'linux', 'win']);
            $deviceId   = 'test-sso-group-' . $num . '-' . $deviceType;
            $this->outputVariables($deviceId);
            if (!$Sso->handle($user, $deviceId, $deviceType, $jwt)) {
                $this->fail((string) $Sso->getError());
            }
            $num++;
        }

        foreach ($expired1 as $ex) {
            try {
                $this->postAuth($ex);
                $this->fail('这里应该返回 401 错误, 不应该正确返回数据');
            } catch (ClientException $e) {
                $this->assertEquals(401, $e->getCode());
            }
        }

        foreach ($expired2 as $ex) {
            try {
                $this->postAuth($ex);
                $this->fail('这里应该返回 401 错误, 不应该正确返回数据');
            } catch (ClientException $e) {
                $this->assertEquals(401, $e->getCode());
            }
        }

        $this->postAuth($success1);
        $this->assertTrue(true, '这里应该正常请求');
        $this->postAuth($success2);
        $this->assertTrue(true, '这里应该正常请求');

        app('poppy.system.setting')->set('py-system::pam.sso_type', $oldSsoType);

        $Sso->banUser($user->id);
    }


    /**
     * @throws GuzzleException
     */
    public function postAuth(string $jwt): void
    {
        $client = new Client();
        $client->post(url('api_v1/system/auth/access'), [
            'headers'     => [
                'Authorization' => "Bearer {$jwt}",
            ],
            'form_params' => [
                '_py_secret' => env('PY_SECRET'),
            ],
        ]);
    }
}