<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Upload;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemUploadFileRequest",
 *     description="文件上传请求 (multipart/form-data). 支持音视频, 不支持图片.",
 *     required={"file", "type"},
 *
 *     @OA\Property(property="file", type="string", format="binary", description="文件内容 (支持多文件上传)"),
 *     @OA\Property(property="type", type="string", description="上传类型", enum={"audio", "video", "images", "file"}, example="audio"),
 *     @OA\Property(property="folder", type="string", nullable=true, description="(4.0) 文件存储目录", default=""),
 *     @OA\Property(property="district", type="integer", nullable=true, description="图片大小限制 (最短边, 默认 1080)", default=1080),
 * )
 */
class UploadFileRequest extends Request
{
    public function getType(): string
    {
        return (string) $this->input('type', 'audio');
    }

    public function getFolder(): string
    {
        return (string) $this->input('folder', '');
    }

    public function getDistrict(): int
    {
        return (int) $this->input('district', 1080);
    }

    public function attributes(): array
    {
        return [
            'file'     => '上传文件',
            'type'     => '类型',
            'folder'   => '文件存储目录',
            'district' => '图片大小限制',
        ];
    }

    public function rules(): array
    {
        return [
            'file' => [
                Rule::required(),
            ],
            'type' => [
                Rule::required(),
                Rule::in(['audio', 'video', 'images', 'file']),
            ],
        ];
    }
}
