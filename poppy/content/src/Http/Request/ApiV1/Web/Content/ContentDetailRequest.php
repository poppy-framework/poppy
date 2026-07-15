<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web\Content;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppyContentContentDetailRequest",
 *     description="内容详情请求",
 *     required={"id"},
 *     @OA\Property(property="id", type="integer", description="内容 ID"),
 *     @OA\Property(property="cat_slug", type="string", nullable=true, description="分类标识 (用于过滤 prev/next)"),
 *     @OA\Property(property="cat_id", type="integer", nullable=true, description="分类 ID"),
 * )
 */
class ContentDetailRequest extends Request
{
    public function getId(): int
    {
        return (int) $this->input('id');
    }

    public function getCatSlug(): string
    {
        return (string) $this->input('cat_slug', '');
    }

    public function getCatId(): int
    {
        return (int) $this->input('cat_id', 0);
    }

    public function attributes(): array
    {
        return [
            'id'       => '内容ID',
            'cat_slug' => '分类标识',
            'cat_id'   => '分类ID',
        ];
    }

    public function rules(): array
    {
        return [
            'id'       => [Rule::required(), Rule::integer()],
            'cat_slug' => [Rule::nullable(), Rule::string()],
            'cat_id'   => [Rule::nullable(), Rule::integer()],
        ];
    }
}