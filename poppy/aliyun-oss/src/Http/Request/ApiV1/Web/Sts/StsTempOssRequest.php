<?php

declare(strict_types = 1);

namespace Poppy\AliyunOss\Http\Request\ApiV1\Web\Sts;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppyAliyunOssStsTempOssRequest",
 *     description="STS 临时授权请求",
 *     @OA\Property(property="is_temp", type="string", nullable=true, description="是否使用临时子目录 (Y=His{rand(8)} 格式; N=按当前日期生成目录)", enum={"Y", "N"}, default="N", example="N"),
 * )
 */
class StsTempOssRequest extends Request
{

    public function getIsTemp(): string
    {
        return (string) $this->get('is_temp', 'N');
    }

    public function attributes(): array
    {
        return [
            'is_temp' => '是否临时目录',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'is_temp' => [
                Rule::in(['Y', 'N']),
            ],
        ];
    }
}