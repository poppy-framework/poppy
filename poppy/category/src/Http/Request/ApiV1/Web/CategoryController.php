<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\Request\ApiV1\Web;

use OpenApi\Annotations as OA;
use Poppy\Category\Action\Category;
use Poppy\Category\Http\Request\ApiV1\Web\Category\CategorySortRequest;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\JwtApiController;

/**
 * 类别排序控制器
 *
 * @OA\Tag(name="Category", description="分类排序 等接口")
 */
class CategoryController extends JwtApiController
{

    /**
     * @OA\Post(
     *     path="/api_v1/category/category/sort",
     *     tags={"Category"},
     *     summary="[Category]排序",
     *     description="调整分类位置. 目标结果: id {position} aim_id, 其中 position=gt 表示 id 大于 aim_id (排在后面), lt 表示 id 小于 aim_id (排在前).",
     *     @OA\RequestBody(
     *         required=true,
     *         description="分类排序请求体, 见 PoppyCategoryCategorySortRequest schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/PoppyCategoryCategorySortRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="已排序",
     *         @OA\JsonContent(ref="#/components/schemas/PoppySystemResponseBody")
     *     ),
     * )
     */
    public function sort(CategorySortRequest $request)
    {
        $id       = $request->getId();
        $type     = (string) $request->getType();
        $aim_id   = $request->getAimId();
        $position = $request->getPosition();
        $Category = new Category();
        if ($Category->sort($type, $id, $position, $aim_id)) {
            return Resp::success('已排序');
        }

        return Resp::error($Category->getError());
    }
}
