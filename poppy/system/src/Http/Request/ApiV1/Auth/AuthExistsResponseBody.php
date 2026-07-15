<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Auth;

use OpenApi\Attributes as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemAuthExistsResponseBody",
 *     description="是否存在用户"
 * )
 */
class AuthExistsResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *    description="是否存在用户",
     *    type="object",
     *    @OA\Property(property="is_exists", description="是否存在用户", type="string"),
     * )
     */
    public object $data;
}
