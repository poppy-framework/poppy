<?php

declare(strict_types = 1);

namespace Poppy\System\Http\Request\ApiV1\Upload;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppySystemUploadImageRequest",
 *     description="图片上传请求 (multipart/form-data)",
 *     required={"image"},
 *
 *     @OA\Property(property="image", type="string", format="binary", description="图片内容 (支持多张/单张上传, type=form 时为文件, type=base64 时为 base64 字符串, type=url 时为远程 URL)"),
 *     @OA\Property(property="type", type="string", nullable=true, description="上传图片类型", enum={"form", "base64", "url"}, default="form", example="form"),
 *     @OA\Property(property="image_type", type="string", nullable=true, description="存储类型, 不同类型存到不同文件夹", default="default", example="default"),
 *     @OA\Property(property="from", type="string", nullable=true, description="上传来源, 影响返回格式 (如 wang-editor)", example=""),
 *     @OA\Property(property="watermark", type="string", nullable=true, description="是否开启水印 (1:开启)", example=""),
 * )
 */
class UploadImageRequest extends Request
{
    public function getType(): string
    {
        return (string) $this->input('type', 'form');
    }

    public function getImageType(): string
    {
        $value = (string) $this->input('image_type', 'default');

        return '' !== $value ? $value : 'default';
    }

    public function getFrom(): string
    {
        return (string) $this->input('from', '');
    }

    public function getWatermark(): bool
    {
        return (bool) $this->input('watermark');
    }

    public function attributes(): array
    {
        return [
            'image'      => '图片内容',
            'type'       => '上传图片的类型',
            'image_type' => '图片存储类型',
            'from'       => '上传来源',
            'watermark'  => '是否开启水印',
        ];
    }

    public function rules(): array
    {
        return [
            'type' => [
                Rule::required(),
                Rule::in(['form', 'base64', 'url']),
            ],
        ];
    }
}
