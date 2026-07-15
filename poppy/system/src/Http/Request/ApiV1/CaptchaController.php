<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1;

use OpenApi\Annotations as OA;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Verification;
use Poppy\System\Classes\Captcha\RequestThrottleService;
use Poppy\System\Events\CaptchaSendEvent;
use Poppy\System\Http\Request\ApiV1\Captcha\CaptchaSendRequest;
use Poppy\System\Http\Request\ApiV1\Captcha\CaptchaVerifyCodeRequest;
use Poppy\System\Models\PamAccount;
use Throwable;

/**
 * 验证码
 */
class CaptchaController extends JwtApiController
{

    /**
     * @OA\Post(
     *     path="/api_v1/system/captcha/send",
     *     tags={"System"},
     *     summary="[Captcha]发送验证码",
     *     description="向指定通行证 (手机号 / 邮箱) 发送一次性验证码. type=exist 时要求通行证存在, type=no-exist 时要求通行证不存在. 命中限流或频繁请求时返回失败.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="发送验证码请求体, 见 SystemCaptchaSendRequest schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/PoppySystemCaptchaSendRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="验证码发送成功",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemResponseBody")
     *     ),
     * )
     */
    public function send(CaptchaSendRequest $request)
    {
        $passport = $request->getPassport();
        $type     = $request->getType();

        if ($type) {
            if ($type === Verification::CAPTCHA_SEND_TYPE_EXIST) {
                if (!PamAccount::passportExists($passport)) {
                    return Resp::error('输入的账号不存在, 请检查输入');
                }
            }
            elseif ($type === Verification::CAPTCHA_SEND_TYPE_NO_EXIST) {
                if (PamAccount::passportExists($passport)) {
                    return Resp::error('输入的账号已存在, 请检查输入');
                }
            }
            else {
                return Resp::error('验证类型有误,请检查输入');
            }
        }

        try {
            // 接口请求限流
            if (app()->has(RequestThrottleService::class) && !app(RequestThrottleService::class)->throttle()) {
                return Resp::error('请求频繁,请稍后重试');
            }
        }
        catch (Throwable $e) {
            return Resp::error('请求频繁,请稍后重试');
        }

        $Verification = new Verification();
        $expired      = ((int) sys_setting('py-system::pam.captcha_expired')) ?: 5;
        $length       = ((int) sys_setting('py-system::pam.captcha_length')) ?: 6;

        if (!$Verification->isPassThrottle('send-' . $passport)) {
            return Resp::error($Verification->getError());
        }
        if ($Verification->genCaptcha($passport, $expired, $length)) {
            $captcha = $Verification->getCaptcha();
            try {
                event(new CaptchaSendEvent($passport, $captcha));
                if (is_production()) {
                    return Resp::success('验证码发送成功');
                }
                return Resp::success('验证码发送成功', [
                    'captcha' => $captcha,
                ]);
            }
            catch (Throwable $e) {
                return Resp::error($e);
            }
        }
        else {
            return Resp::error($Verification->getError());
        }
    }


    /**
     * @OA\Post(
     *     path="/api_v1/system/captcha/verify_code",
     *     tags={"System"},
     *     summary="[Captcha]生成验证串",
     *     description="通过通行证 + 验证码兑换一次性 verify_code, 隐藏字串为 passport. verify_code 可作为后续重置密码 / 换绑手机等流程的凭据. expire_min 默认 10 分钟, 范围 1~60.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="生成验证串请求体, 见 SystemCaptchaVerifyCodeRequest schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/PoppySystemCaptchaVerifyCodeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="生成验证串成功",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemCaptchaVerifyCodeResponseBody")
     *     ),
     * )
     */
    public function verifyCode(CaptchaVerifyCodeRequest $request)
    {
        $passport   = $request->getPassport();
        $captcha    = $request->getCaptcha();
        $expire_min = $request->getExpireMin();

        $Verification = new Verification();
        if (!$Verification->checkCaptcha($passport, $captcha)) {
            return Resp::error($Verification->getError());
        }
        $onceCode = $Verification->genOnceVerifyCode($expire_min, $passport);
        return Resp::success('生成验证串', [
            'verify_code' => $onceCode,
        ]);
    }
}