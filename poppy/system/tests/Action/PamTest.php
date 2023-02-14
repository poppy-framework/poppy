<?php

namespace Poppy\System\Tests\Action;

use Poppy\Framework\Application\TestCase;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Action\Pam;
use Poppy\System\Action\Verification;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Tests\Testing\TestingPam;
use Throwable;

class PamTest extends TestCase
{

    /**
     * 验证码注册
     * @throws ApplicationException
     */
    public function testCaptchaLogin(): void
    {
        // 一个虚拟手机号
        $mobile = $this->faker()->phoneNumber;

        // 发送验证码
        $Verification = new Verification();
        if (!$Verification->genCaptcha($mobile)) {
            $this->fail($Verification->getError());
        }

        $platform = collect(array_keys(PamAccount::kvPlatform()))->random(1)[0];
        $Pam      = new Pam();
        try {
            if ($Pam->captchaLogin($mobile, $Verification->getCaptcha(), 'user', $platform)) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * 空密码注册
     * @throws ApplicationException
     */
    public function testRegisterWithEmptyPassword(): void
    {
        // 一个虚拟手机号
        $mobile = $this->faker()->phoneNumber;

        $Pam = new Pam();
        try {
            if ($Pam->register($mobile)) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @throws ApplicationException
     */
    public function testRegisterWithUsername(): void
    {
        $passport = $this->faker()->lexify('testing_username_????????');
        $password = $this->faker()->lexify('????????');
        $Pam      = new Pam();
        try {
            if ($Pam->register($passport, $password)) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @throws ApplicationException
     */
    public function testRegisterDevelop(): void
    {
        $passport = $this->faker()->lexify('testing_dev_????????');
        $Pam      = new Pam();
        try {
            if ($Pam->register($passport, '', PamRole::DEV_USER)) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }

        $passport = $this->faker()->lexify('testing_dev2_????????');
        try {
            if ($Pam->register($passport, '', [PamRole::DEV_USER])) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
        $passport = $this->faker()->lexify('testing_dev3_????????');
        $roleId   = PamRole::where('name', PamRole::DEV_USER)->value('id');
        try {
            if ($Pam->register($passport, '', [$roleId])) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }

        $passport = $this->faker()->lexify('testing_dev4_????????');
        try {
            if ($Pam->register($passport, '', $roleId)) {
                $this->assertTrue(true);
            }
            else {
                $this->fail($Pam->getError());
            }
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }


    /**
     * @throws ApplicationException
     */
    public function testRebind(): void
    {
        $pam    = TestingPam::randUser();
        $mobile = $this->faker()->phoneNumber;
        $Pam    = new Pam();
        if ($Pam->rebind($pam, $mobile)) {
            $this->assertTrue(true);
        }
        else {
            $this->fail($Pam->getError());
        }
    }

    /**
     * 设置密码
     * @throws ApplicationException
     */
    public function testSetPassword(): void
    {
        $pam      = TestingPam::randUser();
        $Pam      = new Pam();
        $password = $this->faker()->bothify('?#?#?#');
        if ($Pam->setPassword($pam, $password)) {
            $this->assertTrue(true);
            try {
                if (!$Pam->loginCheck($pam->mobile, $password)) {
                    $this->fail($Pam->getError());
                }
                else {
                    $this->assertTrue(true);
                }
            } catch (ApplicationException $e) {
                $this->fail($e->getMessage());
            }
        }
        else {
            $this->fail($Pam->getError());
        }
    }
}