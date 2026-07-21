<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Api\Web\Sso;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="DemoSsoAccessResponseBody",
 *     description="SSO 访问响应 (返回当前通过 SSO 认证的 PAM 账号信息)"
 * )
 */
class SsoAccessResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="PAM 账号信息",
     *     type="object",
     *     @OA\Property(property="id", type="integer", description="PAM 账号 ID"),
     * )
     */
    public object $data;
}
