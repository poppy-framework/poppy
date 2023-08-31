<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web;

use Illuminate\Http\JsonResponse;
use Poppy\Category\Models\SysCategory;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\JwtApiController;
use Poppy\System\Models\SysConfig;

/**
 * 内容控制器
 */
class ContentController extends JwtApiController
{

    /**
     * @api                   {post} api_v1/content/content/lists 内容列表
     * @apiDescription        获取内容列表
     * @apiVersion            1.0.0
     * @apiName               ContentContentList
     * @apiGroup              Poppy
     * @apiQuery {string}     [cat_slug]     分类标识
     * @apiSuccess {object[]} list              列表
     * @apiSuccess {string}   list.slug         标识
     * @apiSuccess {string}   list.path         路径
     * @apiSuccess {string}   list.title        标题
     * @apiSuccess {string}   list.thumb        缩略图
     * @apiSuccess {string}   list.author       作者
     * @apiSuccess {string}   list.create_at    创作时间
     *
     * @apiSuccessExample {json} data
     *  {
     *      "list": [
     *          {
     *              "slug": "",
     *              "path": "content",
     *              "title": "3",
     *              "thumb": "https://test-oss.iliexiang.com/dev/default/202303/27/14/13532wv9xVNe.jpg",
     *              "author": "",
     *              "create_at": "0000-00-00 00:00:00"
     *          }
     *      ]
     *  }
     */
    public function lists(): JsonResponse
    {
        $cat_slug = input('cat_slug');
        $Db       = SysContent::where('is_enable', SysConfig::YES)
            ->orderBy('list_order', 'desc');
        if ($cat_slug && $id = SysCategory::kvNameRefId($cat_slug)) {
            $Db->where('cat_id', $id);
        }
        return SysContent::paginationInfo($Db, function (SysContent $item) {
            return [
                'slug'      => $item->slug,
                'path'      => $item->cat_id ? SysCategory::kvSlug($item->cat_id) : 'content',
                'title'     => $item->title,
                'thumb'     => $item->thumb,
                'author'    => $item->author,
                'create_at' => $item->create_at
            ];
        });
    }

    /**
     * @api                 {post} api_v1/content/content/detail 内容详细
     * @apiDescription      获取详细内容
     * @apiVersion          1.0.0
     * @apiName             ContentContentDetail
     * @apiGroup            Poppy
     * @apiQuery  {int}     [cat_id]      分类ID
     * @apiQuery  {int}     slug          内容 Slug
     *
     * @apiSuccess {string}   title       标题
     * @apiSuccess {string}   author      作者
     * @apiSuccess {string}   create_at   创作时间
     * @apiSuccess {string}   content     内容
     * @apiSuccess {string}   cat_title   分类标题
     * @apiSuccess {object}   prev        上一条
     * @apiSuccess {string}   [prev.path] 路径
     * @apiSuccess {string}   [prev.slug] 标识
     * @apiSuccess {object}   next        下一条
     * @apiSuccess {string}   [next.path] 路径
     * @apiSuccess {string}   [next.slug] 标识
     *
     * @apiSuccessExample {json} data
     *  {
     *      "title": "标题",
     *      "author": "作者",
     *      "create_at": "2023-08-30 16:04:00",
     *      "content": "<p>内容管理</p>",
     *      "cat_title": "默认 1'",
     *      "prev": [
     *          "path": "content",
     *          "slug": "slug-a"
     *      ],
     *      "next": [
     *          "path": "content",
     *          "slug": "slug-b"
     *      ]
     *  }
     */
    public function detail()
    {
        $slug     = input('slug');
        $cat_slug = input('cat_slug');

        $item = SysContent::where('slug', $slug)->firstOrFail();

        $Db     = SysContent::select(['cat_id', 'id', 'title']);
        $DbNext = (clone $Db)->where('id', '>', $item->id)->orderBy('id');
        $DbPrev = (clone $Db)->where('id', '<', $item->id)->orderBy('id', 'desc');

        // 筛选分类
        if ($cat_slug && $cat_id = SysCategory::kvNameRefId($cat_slug)) {
            $DbNext->where('cat_id', $cat_id);
            $DbPrev->where('cat_id', $cat_id);
        }

        /** @var SysContent $next */
        $next = $DbNext->first();
        /** @var SysContent $prev */
        $prev = $DbPrev->first();
        return Resp::success('已获取', [
            'title'     => $item->title,
            'author'    => $item->author,
            'create_at' => $item->create_at,
            'content'   => $item->content,
            'cat_title' => $item->cat_id ? SysCategory::kvTitle($item->cat_id) : '',
            'prev'      => $prev ? [
                'path'  => $prev->cat_id ? SysCategory::kvSlug($prev->cat_id) : 'content',
                'slug'  => (string) $prev->slug,
                'title' => $prev->title,
            ] : (object) [],
            'next'      => $next ? [
                'path'  => $next->cat_id ? SysCategory::kvSlug($next->cat_id) : 'content',
                'slug'  => (string) $next->slug,
                'title' => $prev->title,
            ] : (object) [],
        ]);
    }
}
