<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web\Content;

use OpenApi\Annotations as OA;
use Poppy\System\Http\OpenApi\BaseResponseBody;

/**
 * @OA\Schema(
 *     schema="PoppyContentContentListsResponseBody",
 *     description="内容列表响应 (分页)"
 * )
 */
class ContentListsResponseBody extends BaseResponseBody
{
    /**
     * @OA\Property(
     *     description="分页数据",
     *     type="object",
     *     @OA\Property(
     *         property="list",
     *         type="array",
     *         description="内容列表",
     *         @OA\Items(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="ID"),
     *             @OA\Property(property="path", type="string", description="分类路径"),
     *             @OA\Property(property="slug", type="string", description="分类标识"),
     *             @OA\Property(property="title", type="string", description="标题"),
     *             @OA\Property(property="thumb", type="string", description="缩略图 URL"),
     *             @OA\Property(property="description", type="string", description="描述摘要"),
     *             @OA\Property(property="author", type="string", description="作者"),
     *             @OA\Property(property="create_at", type="string", description="创作时间")
     *         )
     *     ),
     *     @OA\Property(
     *         property="pagination",
     *         type="object",
     *         description="分页信息",
     *         @OA\Property(property="total", type="integer", description="总数"),
     *         @OA\Property(property="page", type="integer", description="当前页"),
     *         @OA\Property(property="size", type="integer", description="每页条数"),
     *         @OA\Property(property="pages", type="integer", description="总页数")
     *     ),
     * )
     */
    public object $data;
}