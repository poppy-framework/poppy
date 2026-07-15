<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Core;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemCoreTranslateResponseBody",
 *     description="多语言包响应"
 * )
 */
class CoreTranslateResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="翻译数据",
     *     type="object",
     *     @OA\Property(property="json", type="boolean", description="是否 JSON 格式", example=true),
     *     @OA\Property(
     *         property="translations",
     *         type="object",
     *         description="键值对形式的多语言翻译 (key => 翻译文本)",
     *         additionalProperties={"type": "string"},
     *         example={"login": "登录", "logout": "退出"}
     *     ),
     * )
     */
    public object $data;
}