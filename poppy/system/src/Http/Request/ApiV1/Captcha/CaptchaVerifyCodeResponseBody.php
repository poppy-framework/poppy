<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Captcha;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemCaptchaVerifyCodeResponseBody",
 *     description="生成验证串响应"
 * )
 */
class CaptchaVerifyCodeResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="生成结果数据",
     *     type="object",
     *     @OA\Property(property="verify_code", type="string", description="一次性验证串, 隐藏字串为 passport"),
     * )
     */
    public object $data;
}
