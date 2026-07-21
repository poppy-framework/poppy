<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthLoginRequest",
 *     required={"passport"},
 *
 *     @OA\Property(property="passport",description="通行证",type="string"),
 *     @OA\Property(property="password",description="密码",type="string",example="123456",),
 *     @OA\Property(property="captcha",description="验证码",type="string",example="1234",),
 *     @OA\Property(property="device_id",description="设备ID",type="string",example="123456",),
 *     @OA\Property(property="device_type",description="设备类型",type="string",example="pc",),
 *     @OA\Property(property="guard",description="登录类型[web|用户(默认);backend|后台;]",type="string",example="web",),
 * )
 */
class AuthLoginRequest extends Request
{
    protected bool $isValidate = false;

    public function attributes(): array
    {
        return [
            'passport' => '通行证',
            'captcha'  => '验证码',
            'password' => '密码',
            'os'       => '软件平台',
        ];
    }

    public function scenes(): array
    {
        return [
            'passport' => [
                'passport', 'os',
            ],
            'captcha'  => [
                'passport', 'captcha', 'os',
            ],
            'password' => [
                'passport', 'password', 'os',
            ],
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
            ],
            'captcha'  => [
                Rule::required(),
                Rule::numeric(),
            ],
            'password' => [
                Rule::required(),
                Rule::string(),
                Rule::simplePwd(),
                Rule::between(6, 20),
            ],
            'os'       => [
                Rule::required(),
                Rule::in(array_keys(PamAccount::kvPlatform())),
            ],
        ];
    }
}
