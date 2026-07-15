<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Upload;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppySystemUploadResponseBody",
 *     description="上传成功响应"
 * )
 */
class UploadResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="上传结果数据",
     *     type="object",
     *     @OA\Property(
     *         property="url",
     *         type="array",
     *         description="上传成功后的文件 URL 列表",
     *         @OA\Items(type="string", format="uri")
     *     ),
     * )
     */
    public object $data;
}