<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;
use Poppy\Category\Models\SysCategory;
use Poppy\Content\Http\Request\ApiV1\Web\Content\ContentDetailRequest;
use Poppy\Content\Http\Request\ApiV1\Web\Content\ContentListsRequest;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\JwtApiController;
use Poppy\System\Models\SysConfig;

/**
 * 内容控制器
 *
 * @OA\Tag(name="Content", description="内容列表 / 详情 等接口")
 */
class ContentController extends JwtApiController
{
    /**
     * @OA\Post(
     *     path="/api_v1/content/content/lists",
     *     tags={"Content"},
     *     summary="[Content]内容列表",
     *     description="获取内容列表 (分页). 可通过 cat_slug 或 cat_id 过滤分类.",
     *
     *     @OA\RequestBody(
     *         required=false,
     *         description="列表请求体",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppyContentContentListsRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="获取列表成功",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppyContentContentListsResponseBody")
     *     ),
     * )
     */
    public function lists(ContentListsRequest $request): JsonResponse
    {
        $catSlug = $request->getCatSlug();
        $catId   = $request->getCatId();
        $Db      = SysContent::where('is_enable', SysConfig::YES)
            ->orderBy('list_order', 'desc');
        if ($catSlug && !$catId) {
            $catId = SysCategory::kvNameRefId($catSlug);
        }
        if ($catId) {
            $Db = $Db->where('cat_id', $catId);
        }

        return SysContent::paginationInfo($Db, function (SysContent $item) {
            return [
                'id'          => $item->id,
                'path'        => $item->cat_id ? SysCategory::kvSlug($item->cat_id) : 'content',  // to be removed
                'slug'        => $item->cat_id ? SysCategory::kvSlug($item->cat_id) : 'content',
                'title'       => $item->title,
                'thumb'       => $item->thumb,
                'description' => $item->description ?: Str::substr(strip_tags($item->content), 0, 150),
                'author'      => $item->author,
                'create_at'   => $item->create_at,
            ];
        });
    }

    /**
     * @OA\Post(
     *     path="/api_v1/content/content/detail",
     *     tags={"Content"},
     *     summary="[Content]内容详情",
     *     description="获取内容详情, 同时返回同分类下的上一条 / 下一条.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="详情请求体, 见 PoppyContentContentDetailRequest schema",
     *
     *         @OA\MediaType(
     *             mediaType="application/json",
     *
     *             @OA\Schema(ref="#/components/schemas/PoppyContentContentDetailRequest")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="已获取",
     *
     *         @OA\JsonContent(ref="#/components/schemas/PoppyContentContentDetailResponseBody")
     *     ),
     * )
     */
    public function detail(ContentDetailRequest $request)
    {
        $id       = $request->getId();
        $catSlug  = $request->getCatSlug();
        $catId    = $request->getCatId();

        $item = SysContent::findOrFail($id);

        $Db     = SysContent::select(['cat_id', 'title', 'id']);
        $DbNext = (clone $Db)->where('id', '>', $item->id)->orderBy('id');
        $DbPrev = (clone $Db)->where('id', '<', $item->id)->orderBy('id', 'desc');

        // 筛选分类
        if ($catSlug && !$catId) {
            $catId = SysCategory::kvNameRefId($catSlug);
        }

        if ($catId) {
            $DbNext->where('cat_id', $catId);
            $DbPrev->where('cat_id', $catId);
        }

        /** @var SysContent $next */
        $next = $DbNext->first();
        /** @var SysContent $prev */
        $prev = $DbPrev->first();

        return Resp::success('已获取', [
            'title'       => $item->title,
            'keyword'     => $item->keyword,
            'description' => $item->description ?: Str::substr(strip_tags($item->content), 0, 150),
            'author'      => $item->author,
            'create_at'   => $item->create_at,
            'content'     => $item->content,
            'cat_title'   => $item->cat_id ? SysCategory::kvTitle($item->cat_id) : '',
            'prev'        => $prev ? [
                'path'  => $prev->cat_id ? SysCategory::kvSlug($prev->cat_id) : 'content',  // to be removed
                'slug'  => $prev->cat_id ? SysCategory::kvSlug($prev->cat_id) : 'content',
                'id'    => $prev->id,
                'title' => $prev->title,
            ] : (object) [],
            'next'        => $next ? [
                'path'  => $next->cat_id ? SysCategory::kvSlug($next->cat_id) : 'content',  // to be removed
                'slug'  => $next->cat_id ? SysCategory::kvSlug($next->cat_id) : 'content',
                'id'    => $next->id,
                'title' => $next->title,
            ] : (object) [],
        ]);
    }
}
