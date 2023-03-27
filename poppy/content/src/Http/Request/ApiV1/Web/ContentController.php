<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\ApiV1\Web;

use Poppy\Content\Action\Content;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Http\Request\ApiV1\JwtApiController;

/**
 * Sts 配置
 */
class ContentController extends JwtApiController
{

    /**
     * @api                 {post} api_v1/content/content/sort [Content]排序
     * @apiDescription      这里的目标结果是 id {before|after} aim_id
     * @apiVersion          1.0.0
     * @apiName             ContentStsTempOss
     * @apiGroup            Poppy
     * @apiQuery   {int}     type         分类分组
     * @apiQuery  {int}     id           ID
     * @apiQuery  {string}  position     ID [前|后]于 目标 ID [before:前;after:后;inner:不排序]
     * @apiQuery  {string}  aim_id       目标ID
     */
    public function sort()
    {
        $id       = (int) input('id');
        $type     = (string) input('type');
        $aim_id   = (int) input('aim_id');
        $position = (string) input('position');
        $Content = new Content();
        if ($Content->sort($type, $id, $position, $aim_id)) {
            return Resp::success('已排序');
        }

        return Resp::error($Content->getError());
    }
}
