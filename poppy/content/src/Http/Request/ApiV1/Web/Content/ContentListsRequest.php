<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web\Content;

use OpenApi\Annotations as OA;
use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PoppyContentContentListsRequest",
 *     description="内容列表请求",
 *     @OA\Property(property="cat_slug", type="string", nullable=true, description="分类标识 (与 cat_id 二选一)"),
 *     @OA\Property(property="cat_id", type="integer", nullable=true, description="分类 ID (优先于 cat_slug)"),
 *     @OA\Property(property="page", type="integer", nullable=true, description="页码, 默认 1", default=1, minimum=1),
 *     @OA\Property(property="size", type="integer", nullable=true, description="每页条数, 默认 20", default=20, minimum=1, maximum=100),
 * )
 */
class ContentListsRequest extends Request
{
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
            'cat_slug' => '分类标识',
            'cat_id'   => '分类ID',
            'page'     => '页码',
            'size'     => '每页条数',
        ];
    }

    public function rules(): array
    {
        return [
            'cat_slug' => [Rule::nullable(), Rule::string()],
            'cat_id'   => [Rule::nullable(), Rule::integer()],
            'page'     => [Rule::nullable(), Rule::integer(), Rule::min(1)],
            'size'     => [Rule::nullable(), Rule::integer(), Rule::between(1, 100)],
        ];
    }
}