<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Http\Request\ApiV1\Web\Sts;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppyAliyunOssStsTempOssResponseBody",
 *     description="STS 临时授权响应"
 * )
 */
class StsTempOssResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="STS 授权数据",
     *     type="object",
     *     @OA\Property(property="directory", type="string", description="允许上传的目录"),
     *     @OA\Property(property="prefix_url", type="string", description="组合上传的 URL 前缀", format="uri"),
     *     @OA\Property(property="bucket", type="string", description="存储桶名称"),
     *     @OA\Property(property="endpoint", type="string", description="OSS endpoint", example="oss-cn-beijing.aliyuncs.com"),
     *     @OA\Property(property="access_key_id", type="string", description="临时访问 Key ID"),
     *     @OA\Property(property="access_key_secret", type="string", description="临时访问 Key Secret"),
     *     @OA\Property(property="security_token", type="string", description="STS 安全令牌"),
     *     @OA\Property(property="expiration", type="string", description="过期时间 (ISO8601)", example="2021-06-02T02:51:45Z"),
     * )
     */
    public object $data;
}
