<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Captcha;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Action\Verification;

/**
 * @OA\Schema(
 *     schema="PoppySystemCaptchaSendRequest",
 *     description="发送验证码请求",
 *     required={"passport"},
 *
 *     @OA\Property(property="passport", type="string", description="通行证 (手机号 / 邮箱)"),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         nullable=true,
 *         description="验证类型: exist=通行证必须存在, no-exist=通行证必须不存在",
 *         enum={"exist", "no-exist"},
 *         example="exist"
 *     ),
 * )
 */
class CaptchaSendRequest extends Request
{
    public function getPassport()
    {
        return $this->input('passport', '');
    }

    public function getType()
    {
        return $this->input('type', '');
    }

    public function attributes(): array
    {
        return [
            'passport' => '通行证',
            'type'     => '验证类型',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'passport' => [
                Rule::required(),
                Rule::string(),
            ],
            'type'     => [
                Rule::nullable(),
                Rule::in([
                    Verification::CAPTCHA_SEND_TYPE_EXIST,
                    Verification::CAPTCHA_SEND_TYPE_NO_EXIST,
                ]),
            ],
        ];
    }
}
