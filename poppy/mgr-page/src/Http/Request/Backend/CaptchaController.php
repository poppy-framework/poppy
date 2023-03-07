<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Action\Verification;
use Poppy\System\Events\CaptchaSendEvent;
use Poppy\System\Models\PamAccount;
use Throwable;
use Validator;

/**
 * 后台登录发送验证码
 */
class CaptchaController extends BackendController
{

    /**
     * 发送后台通行证的验证码
     * @return JsonResponse|RedirectResponse|Response
     */
    public function send()
    {
        $validator = Validator::make(input(), [
            'mobile'  => [
                Rule::required(),
                Rule::mobile(),
            ],
            'captcha' => 'required|captcha',
        ], [], [
            'mobile' => '手机号',
            'code'   => '验证码',
        ]);

        $mobile = input('mobile');
        if ($validator->fails()) {
            return Resp::error($validator->messages());
        }

        $beMobile = PamAccount::beMobile($mobile);
        if (!PamAccount::where('type', PamAccount::TYPE_BACKEND)->where('mobile', $beMobile)->exists()) {
            return Resp::error('用户不存在');
        }

        $Verification = new Verification();
        $expired      = (int) sys_setting('py-system::pam.captcha_expired') ?: 5;
        if ($Verification->genCaptcha($mobile, $expired)) {
            $captcha = $Verification->getCaptcha();
            try {
                event(new CaptchaSendEvent($mobile, $captcha));
                return Resp::success('验证码发送成功' . (!is_production() ? ', 验证码:' . $captcha : ''));
            } catch (Throwable $e) {
                return Resp::error($e);
            }
        }
        else {
            return Resp::error($Verification->getError());
        }
    }
}