<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthRenewResponseBody",
 *     description="续期的数据"
 * )
 */
class AuthRenewResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *    type="object",
     *    @OA\Property(property="token", description="Token", type="string"),
     *    @OA\Property(property="type", description="类型", type="string")
     * )
     */
    public object $data;
}
