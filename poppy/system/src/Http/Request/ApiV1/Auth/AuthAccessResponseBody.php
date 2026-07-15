<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthAccessResponseBody",
 *     description="登录成功",
 * )
 */
class AuthAccessResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *    description="登录成功返回的token信息",
     *    type="object",
     *    @OA\Property(property="id", description="ID", type="integer"),
     *    @OA\Property(property="username", description="用户名", type="string"),
     *    @OA\Property(property="mobile", description="手机号", type="string"),
     *    @OA\Property(property="email", description="邮箱", type="string"),
     *    @OA\Property(property="type", description="类型", type="string"),
     *    @OA\Property(property="is_enable", description="是否启用(Y|N)", type="string"),
     *    @OA\Property(property="disable_reason", description="禁用原因", type="string"),
     *    @OA\Property(property="created_at", description="创建时间", type="string"),
     *    @OA\Property(property="updated_at", description="更新时间", type="string")
     * )
     */
    public object $data;
}
