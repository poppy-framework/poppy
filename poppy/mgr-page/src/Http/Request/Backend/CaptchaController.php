<?php
declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Mews\Captcha\Facades\Captcha;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Http\Request\ApiV1\CaptchaController as BaseCaptchaController;
use Validator;
use DB;

class CaptchaController extends BackendController
{
    public function send()
    {
        if (is_post()) {
            $validator = Validator::make(input(), [
                'passport'   => [
                    Rule::required(),
                    Rule::mobile(),
                ],
                'username' => [
                    Rule::required(),
                ],
                'code'     => 'required|captcha',
            ], [], [
                'mobile' => '手机号',
                'code'   => '验证码',
            ]);

            if ($validator->fails()) {
                return Resp::error($validator->messages());
            }

            $username = input('username');
            $mobile   = input('passport');
            if (!DB::table('pam_backend')->where('username', $username)
                ->where('mobile', $mobile)->exists()) {
                return Resp::error('用户不存在');
            }

            // 图形验证码验证
            return (new BaseCaptchaController())->send();
        }

        return view('py-mgr-page::backend.home.captcha', input());
    }
}