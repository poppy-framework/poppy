<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Captcha;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemCaptchaVerifyCodeRequest",
 *     description="生成验证串请求",
 *     required={"passport", "captcha"},
 *     @OA\Property(property="passport", type="string", description="通行证 (手机号 / 邮箱)"),
 *     @OA\Property(property="captcha", type="string", description="验证码原文"),
 *     @OA\Property(
 *         property="expire_min",
 *         type="integer",
 *         nullable=true,
 *         description="验证串有效期 (分钟), 默认 10, 范围 1~60",
 *         default=10,
 *         minimum=1,
 *         maximum=60
 *     ),
 * )
 */
class CaptchaVerifyCodeRequest extends Request
{

    public function getPassport(): string
    {
        return (string) $this->input('passport', '');
    }

    public function getCaptcha(): string
    {
        return (string) $this->input('captcha', '');
    }

    /**
     * 获取有效期 (分钟). 默认 10, 范围 1~60.
     * 在 getter 阶段 clamp, 与原 verifyCode() 行为保持一致.
     */
    public function getExpireMin(): int
    {
        $value = (int) $this->input('expire_min', 10);
        if ($value > 60) {
            return 60;
        }
        if ($value < 1) {
            return 1;
        }
        return $value;
    }

    public function attributes(): array
    {
        return [
            'passport'   => '通行证',
            'captcha'    => '验证码',
            'expire_min' => '验证串有效期 (分钟)',
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
            'passport'   => [
                Rule::required(),
                Rule::string(),
            ],
            'captcha'    => [
                Rule::required(),
                Rule::string(),
            ],
            'expire_min' => [
                Rule::nullable(),
                Rule::integer(),
                Rule::between(1, 60),
            ],
        ];
    }
}