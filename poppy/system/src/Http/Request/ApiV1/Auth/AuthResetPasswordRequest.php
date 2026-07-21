<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthResetPasswordRequest",
 *     description="重设密码",
 *     required={"password"},
 *
 *     @OA\Property(property="verify_code", type="string", description="方式1: 通过验证码获取到的 验证串"),
 *     @OA\Property(property="passport", type="string", description="方式2: 手机号 + 验证码直接验证并修改"),
 *     @OA\Property(property="captcha", type="string", description="验证码 (方式2 时必填)"),
 *     @OA\Property(property="password", type="string", description="新密码")
 * )
 */
class AuthResetPasswordRequest extends Request
{
    public function getPwd(): string
    {
        return (string) $this->input('password');
    }

    public function getVerifyCode(): string
    {
        return (string) $this->input('verify_code');
    }

    public function getPassport(): string
    {
        return (string) $this->input('passport');
    }

    public function getCaptcha(): string
    {
        return (string) $this->input('captcha');
    }

    public function attributes(): array
    {
        return [
            'password'    => '密码',
            'verify_code' => '验证码',
            'passport'    => '手机号',
            'captcha'     => '验证码',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'password' => [
                Rule::required(),
                Rule::string(),
                Rule::simplePwd(),
                Rule::between(6, 20),
            ],
        ];
    }
}
