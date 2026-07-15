<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web\Content;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppyContentContentDetailResponseBody",
 *     description="内容详情响应"
 * )
 */
class ContentDetailResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="内容详情",
     *     type="object",
     *     @OA\Property(property="title", type="string", description="标题"),
     *     @OA\Property(property="keyword", type="string", description="关键词"),
     *     @OA\Property(property="description", type="string", description="描述"),
     *     @OA\Property(property="author", type="string", description="作者"),
     *     @OA\Property(property="create_at", type="string", description="创作时间"),
     *     @OA\Property(property="content", type="string", description="正文 HTML"),
     *     @OA\Property(property="cat_title", type="string", description="分类标题"),
     *     @OA\Property(property="prev", type="object", description="上一条", additionalProperties=true),
     *     @OA\Property(property="next", type="object", description="下一条", additionalProperties=true),
     * )
     */
    public object $data;
}