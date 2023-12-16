<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Http\Request\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Poppy\Backstage\Action\Manager;
use Poppy\Backstage\Http\Validation\MgrLoginRequest;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\StrHelper;
use Poppy\System\Action\Pam;
use Poppy\System\Action\Verification;
use Poppy\System\Events\LoginTokenPassedEvent;
use Poppy\System\Http\Request\ApiV1\JwtApiController;
use Poppy\System\Http\Validation\PamPasswordRequest;
use Poppy\System\Models\PamAccount;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 认证控制器
 */
class AuthController extends JwtApiController
{
    use ThrottlesLogins;

    /**
     * 最大请求次数 10 次
     * @var float|int
     */
    protected float $maxAttempts = 2;

    /**
     * 30 秒内, 最多 10 次请求
     * @var float
     */
    protected float $decayMinutes = 0.5;


    public function access(): JsonResponse
    {
        return Resp::success('有效登录', [
            'username' => StrHelper::hideContact($this->pam()->username)
        ]);
    }

    /**
     * @param Request $originRequest
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws Throwable
     */
    public function login(Request $originRequest): JsonResponse
    {
        $originRequest->merge([
            'x-os' => x_header('os'),
            'x-id' => x_header('id'),
        ]);
        /** @var MgrLoginRequest $request */
        $request = app(MgrLoginRequest::class, [$originRequest]);

        // 进行必要参数验证
        $request->scene('passport')->validateResolved();

        // 频率限制
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->sendLockoutResponse($request);
        }

        // 类型拦截
        if (!$request->getCaptcha() && !$request->getPassword()) {
            return Resp::error('登录密码或者验证码必须填写');
        }


        $Pam = new Manager();
        if ($request->getCaptcha()) {
            $request->scene('captcha')->validateResolved();
            if (!$Pam->captchaLogin($request)) {
                $this->incrementLoginAttempts($request);
                return Resp::error($Pam->getError());
            }
        }
        else {
            // use password
            $request->scene('password')->validateResolved();
            if (!$Pam->loginCheck($request)) {
                $this->incrementLoginAttempts($request);
                return Resp::error($Pam->getError());
            }
        }

        $this->clearLoginAttempts($request);

        $pam   = $Pam->getPam();
        $token = JWTAuth::fromUser($pam);

        try {
            // 设备单一性登陆验证(基于 Redis + Db)
            event(new LoginTokenPassedEvent($pam, $token, $request->getId(), $request->getOs()));
        } catch (Throwable $e) {
            return Resp::error($e->getMessage());
        }

        return Resp::success('登录成功', [
            'token' => $token
        ]);
    }


    /**
     * @throws Throwable
     * @api                   {post} /api_v1/system/auth/reset_password [Sys]重设密码
     * @apiVersion            1.0.0
     * @apiName               SysAuthResetPassword
     * @apiGroup              Poppy
     * @apiQuery {string}     [verify_code]     方式1: 通过验证码获取到-> 验证串
     * @apiQuery {string}     [passport]        方式2: 手机号 + 验证码直接验证并修改
     * @apiQuery {string}     [captcha]         验证码
     * @apiQuery {string}     password          密码
     */
    public function resetPassword(PamPasswordRequest $request)
    {
        $verify_code = input('verify_code', '');
        $password    = $request->input('password');
        $passport    = input('passport', '');
        $captcha     = input('captcha', '');

        $Verification = new Verification();
        if ((!$verify_code && !$passport) || ($verify_code && $passport)) {
            return Resp::error('请选一种方式重设密码!');
        }

        // 获取通行证
        $useVerify = false;
        if (!$passport) {
            $useVerify = true;
            if (!$Verification->verifyOnceCode($verify_code, false)) {
                return Resp::error($Verification->getError());
            }
            $passport = $Verification->getHidden();
        }
        else if (!$captcha || !$Verification->checkCaptcha($passport, $captcha, false)) {
            return Resp::error('请输入正确验证码');
        }

        $pam = PamAccount::passport($passport);
        if (!$pam) {
            return Resp::error('此账号不存在');
        }
        $Pam = new Pam();
        if (!$Pam->checkPwdStrength($pam->type, $password)) {
            return Resp::error($Pam->getError());
        }

        if ($Pam->setPassword($pam, $password)) {
            if ($useVerify) {
                $Verification->removeOnceCode($verify_code);
            }
            else {
                $Verification->removeCaptcha($passport);
            }

            return Resp::success('密码已经重新设置');
        }

        return Resp::error($Pam->getError());
    }


    /**
     * @api                   {post} /api_v1/system/auth/logout [Sys]退出登录
     * @apiVersion            1.0.0
     * @apiName               SysAuthLogout
     * @apiGroup              Poppy
     */

    /**
     * @return JsonResponse|RedirectResponse|Response
     * @throws Throwable
     */
    public function logout()
    {
        (new Pam())->setPam($this->pam())->logout();
        return Resp::success('已退出登录');
    }

    protected function username(): string
    {
        return 'passport';
    }
}