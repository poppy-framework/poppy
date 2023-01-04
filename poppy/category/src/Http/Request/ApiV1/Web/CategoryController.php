<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\Request\ApiV1\Web;

use Poppy\Category\Action\Category;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\JwtApiController;

/**
 * Sts 配置
 */
class CategoryController extends JwtApiController
{

    /**
     * @api                 {post} api_v1/category/category/sort [Category]排序
     * @apiDescription      这里的目标结果是 id {before|after} aim_id
     * @apiVersion          1.0.0
     * @apiName             CategoryStsTempOss
     * @apiGroup            Poppy
     * @apiParam  {int}     type         分类分组
     * @apiParam  {int}     id           ID
     * @apiParam  {string}  position     ID [前|后]于 目标 ID [before:前;after:后;inner:不排序]
     * @apiParam  {string}  aim_id       目标ID
     */
    public function sort()
    {
        $id       = (int) input('id');
        $type     = (string) input('type');
        $aim_id   = (int) input('aim_id');
        $position = (string) input('position');
        $Category = new Category();
        if ($Category->sort($type, $id, $position, $aim_id)) {
            return Resp::success('已排序');
        }

        return Resp::error($Category->getError());
    }
}
