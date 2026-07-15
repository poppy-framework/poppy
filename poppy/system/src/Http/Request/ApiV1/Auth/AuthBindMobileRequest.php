<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthBindMobileRequest",
 *     description="绑定手机号",
 *     required={"verify_code", "passport", "captcha"},
 *     @OA\Property(property="verify_code", type="string", description="方式1: 通过验证码获取到的 验证串"),
 *     @OA\Property(property="passport", type="string", description="方式2: 手机号 + 验证码直接验证并修改"),
 *     @OA\Property(property="captcha", type="string", description="验证码 (方式2 时必填)"),
 * )
 */
class AuthBindMobileRequest extends Request
{
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
            'verify_code' => '验证码',
            'passport'    => '手机号',
            'captcha'     => '验证码',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'passport' => [
                Rule::required(),
                Rule::string(),
            ],
            'captcha' => [
                Rule::required(),
                Rule::string(),
            ],
            'verify_code' => [
                Rule::required(),
                Rule::string(),
            ],
        ];
    }
}
