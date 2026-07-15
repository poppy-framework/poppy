<?php

declare(strict_types = 1);

namespace Poppy\Area\Http\Request\ApiV1\Web;

use OpenApi\Annotations as OA;
use Poppy\Area\Http\Request\ApiV1\Web\Area\AreaCodeResponseBody;
use Poppy\Area\Http\Request\ApiV1\Web\Area\AreaCountryResponseBody;
use Poppy\Area\Models\SysArea;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\System\Http\Request\ApiV1\WebApiController;

/**
 * 地区管理控制器
 *
 * @OA\Tag(name="Area", description="地区代码 / 国别 等接口")
 */
class AreaController extends WebApiController
{

    /**
     * @OA\Post(
     *     path="/api_v1/area/area/code",
     *     tags={"Area"},
     *     summary="[Area]地区代码",
     *     description="获取地区代码 (树形结构). code 字段为地区编码左 6 位.",
     *     @OA\Response(
     *         response=200,
     *         description="获取数据成功",
     *         @OA\JsonContent(ref="#/components/schemas/PoppyAreaAreaCodeResponseBody")
     *     ),
     * )
     */
    public function code()
    {
        $items = SysArea::selectRaw("id,title,left(code, 6) as code,parent_id")->get()->toArray();
        $array = UtilHelper::genTree($items, 'id', 'parent_id', 'children', false);
        return Resp::success('获取数据成功', $array);
    }


    /**
     * @OA\Post(
     *     path="/api_v1/area/area/country",
     *     tags={"Area"},
     *     summary="[Area]国别",
     *     description="获取国家代码 (键值对, code => 国家名称).",
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref="#/components/schemas/PoppyAreaAreaCountryResponseBody")
     *     ),
     * )
     */
    public function country()
    {
        return Resp::success(
            '获取成功',
            SysArea::country()
        );
    }
}