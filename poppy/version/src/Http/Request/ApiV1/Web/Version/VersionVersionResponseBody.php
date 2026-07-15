<?php

declare(strict_types = 1);

namespace Poppy\Version\Http\Request\ApiV1\Web\Version;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppyVersionVersionVersionResponseBody",
 *     description="版本检测响应"
 * )
 */
class VersionVersionResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="版本数据",
     *     type="object",
     *     @OA\Property(property="download_url", type="string", description="下载地址", format="uri"),
     *     @OA\Property(property="description", type="string", description="更新描述"),
     *     @OA\Property(property="version", type="string", description="最新版本号"),
     *     @OA\Property(property="is_upgrade", type="string", description="是否需要强制更新 (Y/N)", enum={"Y", "N"}),
     * )
     */
    public object $data;
}