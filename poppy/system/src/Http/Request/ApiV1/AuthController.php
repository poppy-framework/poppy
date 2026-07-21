<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\System\Action\Pam;
use Poppy\System\Action\Verification;
use Poppy\System\Events\LoginSuccessEvent;
use Poppy\System\Events\LoginTokenPassedEvent;
use Poppy\System\Events\TokenRenewEvent;
use Poppy\System\Http\Request\ApiV1\Auth\AuthBindMobileRequest;
use Poppy\System\Http\Request\ApiV1\Auth\AuthExistsRequest;
use Poppy\System\Http\Request\ApiV1\Auth\AuthLoginRequest;
use Poppy\System\Http\Request\ApiV1\Auth\AuthRenewRequest;
use Poppy\System\Http\Request\ApiV1\Auth\AuthResetPasswordRequest;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\Resources\PamResource;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 认证控制器
 *
 * @OA\Tag(name="System", description="认证 / 登录 / Token 等接口")
 */
class AuthController extends JwtApiController
{
    use ThrottlesLogins;

    /**
     * 最大请求次数 10 次
     *
     * @var float|int
     */
    protected float $maxAttempts = 10;

    /**
     * 30 秒内, 最多 10 次请求
     */
    protected float $decayMinutes = 0.5;

    /**
     * @OA\Post(
     *     path="/api_v1/system/auth/access",
     *     tags={"System"},
     *     summary="[Auth]检测 Token",
     *     description="校验当前请求 Token 是否有效, 返回 PAM 账号信息.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="有效登录",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemAuthAccessResponseBody")
     *     ),
     * )
     */
    public function access(Request $request): JsonResponse
    {
        $pam    = (new PamResource($this->pam()))->toArray($request);
        $append = (array) sys_hook('poppy.system.auth_access');
        $all    = array_merge($pam, $append);

        return Resp::success(
            '有效登录',
            $all
        );
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/auth/login",
     *     tags={"System"},
     *     summary="[Auth]登录/注册",
     *     description="通过密码或短信验证码登录账号, 不存在账号会自动注册.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="登录请求体, 见 AuthLoginRequest schema",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppySystemAuthLoginRequest")
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="x-os",
     *         in="header",
     *         description="OS 平台类型 (例如 ios, android, pc)",
     *
     *         @OA\Schema(type="string", example="pc")
     *     ),
     *
     *     @OA\Parameter(
     *         name="x-type",
     *         in="header",
     *         description="账号类型 (例如 backend, web)",
     *
     *         @OA\Schema(type="string", example="backend")
     *     ),
     *
     *     @OA\Parameter(
     *         name="x-id",
     *         in="header",
     *         description="设备 ID",
     *
     *         @OA\Schema(type="string", example="123456")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemAuthLoginResponseBody")
     *     )
     * )
     *
     * @throws Throwable
     */
    public function login(Request $req): JsonResponse
    {
        $req->merge([
            'os' => ((string) $req->input('device_type', '')) ?: x_header('os'),
        ]);
        /** @var AuthLoginRequest $request */
        $request     = app(AuthLoginRequest::class, [$req]);
        $reqPassport = $request->scene('passport')->validated();

        // 频率限制
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->sendLockoutResponse($request);
        }
        $this->incrementLoginAttempts($request);

        // 类型拦截
        if (!$request->input('captcha') && !$request->input('password')) {
            return Resp::error('登录密码或者验证码必须填写');
        }

        // 登录类型
        $guard = (((string) $request->input('guard')) ?: x_header('type')) === PamAccount::TYPE_BACKEND
            ? PamAccount::GUARD_JWT_BACKEND
            : PamAccount::GUARD_JWT_WEB;

        $Pam = new Pam();
        if ($request->input('captcha')) {
            $reqCaptcha = $request->scene('captcha')->validated();
            if (!$Pam->captchaLogin($reqCaptcha['passport'], $reqCaptcha['captcha'], $guard)) {
                return Resp::error($Pam->getError());
            }
        }
        else {
            // use password

            $reqPwd   = $request->scene('password')->validated();
            $passport = PamAccount::fullFilledPassport($reqPwd['passport']);
            if (!$Pam->loginCheck($passport, $reqPwd['password'], $guard)) {
                return Resp::error($Pam->getError());
            }
        }

        $this->clearLoginAttempts($request);

        $pam   = $Pam->getPam();
        $token = JWTAuth::fromUser($pam);

        /* 设备单一性登陆验证(基于 Redis + Db)
         * ---------------------------------------- */
        try {
            $deviceId = x_header('id') ?: (string) $request->input('device_id', '');
            event(new LoginTokenPassedEvent($pam, $token, $deviceId, $reqPassport['os']));
        }
        catch (Throwable $e) {
            return Resp::error($e->getMessage());
        }

        return Resp::success('登录成功', [
            'token'       => $token,
            'type'        => $pam->type,
            'is_register' => $Pam->getIsRegister() ? 'Y' : 'N',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/auth/reset_password",
     *     tags={"System"},
     *     summary="[Auth]重设密码",
     *     description="通过验证码或 verify_code 重设密码. verify_code 与 passport/captcha 两种方式二选一.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppySystemAuthResetPasswordRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemResponseBody")
     *     )
     * )
     */
    public function resetPassword(AuthResetPasswordRequest $request)
    {
        $verify_code = $request->getVerifyCode();
        $passport    = $request->getPassport();
        $captcha     = $request->getCaptcha();
        $password    = $request->getPwd();

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
        elseif (!$captcha || !$Verification->checkCaptcha($passport, $captcha, false)) {
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
     * @OA\Post(
     *     path="/api_v1/system/auth/bind_mobile",
     *     tags={"System"},
     *     summary="[Auth]换绑手机",
     *     description="解绑并换绑当前 PAM 账号到新手机号.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppySystemAuthBindMobileRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemResponseBody")
     *     )
     * )
     */
    public function bindMobile(AuthBindMobileRequest $request)
    {
        $captcha     = $request->getCaptcha();
        $passport    = $request->getPassport();
        $verify_code = $request->getVerifyCode();

        if (!UtilHelper::isMobile($passport)) {
            return Resp::error('请输入正确手机号');
        }

        $Verification = new Verification();
        if (!$Verification->checkCaptcha($passport, $captcha)) {
            return Resp::error('请输入正确验证码');
        }

        if ($verify_code && !$Verification->verifyOnceCode($verify_code)) {
            return Resp::error($Verification->getError());
        }

        $hidden = $Verification->getHidden();

        $Pam = new Pam();
        if (!$Pam->rebind($hidden, $passport)) {
            return Resp::error($Pam->getError());
        }

        return Resp::success('成功绑定手机');
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/auth/renew",
     *     tags={"System"},
     *     summary="[Auth]凭证续期",
     *     description="使用当前 JWT 续签一个新的 Token.",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppySystemAuthRenewRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemAuthRenewResponseBody")
     *     )
     * )
     */
    public function renew(AuthRenewRequest $request)
    {
        $pam   = $this->pam;
        $token = JWTAuth::fromUser($pam);

        try {
            $deviceId   = x_header('id') ?: $request->getDeviceId();
            $deviceType = x_header('os') ?: $request->getDeviceType();

            event(new TokenRenewEvent($pam, $token, $deviceId, $deviceType));
        }
        catch (Throwable $e) {
            return Resp::error($e->getMessage());
        }

        event(new LoginSuccessEvent($this->pam, 'jwt', 'renew'));

        return Resp::success('续期成功', [
            'token' => $token,
            'type'  => $pam->type,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/auth/logout",
     *     tags={"System"},
     *     summary="[Auth]退出登录",
     *     description="退出当前 JWT 登录态.",
     *
     *     @OA\Response(response=200, description="已退出登录"),
     * )
     *
     * @return JsonResponse|RedirectResponse|Response
     *
     * @throws Throwable
     */
    public function logout()
    {
        (new Pam())->setPam($this->pam())->logout();

        return Resp::success('已退出登录');
    }

    /**
     * @OA\Post(
     *     path="/api_v1/system/auth/exists",
     *     tags={"System"},
     *     summary="[Auth]检查通行证是否存在",
     *     description="检查指定通行证 (手机号 / 邮箱) 是否在系统中存在. 存在返回成功, 不存在返回失败.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppySystemAuthExistsRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="通行证存在",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemAuthExistsResponseBody")
     *     ),
     * )
     */
    public function exists(AuthExistsRequest $request)
    {
        $passport = $request->getPassport();
        $is_data  = $request->getIsData();
        $exists   = PamAccount::passportExists($passport);

        if ($exists) {
            if ('Y' === $is_data) {
                return Resp::success('通行证存在', [
                    'is_exist' => 'Y',
                ]);
            }

            return Resp::success('通行证存在');
        }
        if ('Y' === $is_data) {
            return Resp::success('通行证不存在', [
                'is_exist' => 'N',
            ]);
        }

        return Resp::error('通行证不存在');
    }

    /**
     * @return float
     */
    public function maxAttempts()
    {
        return (int) env('THROTTLES_MAX_ATTEMPTS', $this->maxAttempts);
    }

    /**
     * @return float
     */
    public function decayMinutes()
    {
        return (float) env('THROTTLES_DECAY_MINUTES', $this->decayMinutes);
    }

    protected function username(): string
    {
        return 'passport';
    }
}
