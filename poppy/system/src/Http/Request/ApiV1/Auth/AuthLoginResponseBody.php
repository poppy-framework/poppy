<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthLoginResponseBody",
 *     description="登录成功"
 * )
 */
class AuthLoginResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *    description="登录成功返回的token信息",
     *    type="object",
     *    @OA\Property(property="token", description="Token", type="string"),
     *    @OA\Property(property="type", description="类型", type="string"),
     *    @OA\Property(property="is_register", description="是否是注册", type="string"),
     * )
     */
    public object $data;
}
