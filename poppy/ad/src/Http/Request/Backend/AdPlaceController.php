<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http\Request\Backend;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Poppy\Ad\Action\Place;
use Poppy\Ad\Http\MgrPage\FormPlaceEstablish;
use Poppy\Ad\Http\MgrPage\ListSysAdPlace;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Throwable;

/**
 * 广告位管理
 */
class AdPlaceController extends BackendController
{
    /**
     * 广告位列表
     *
     * @throws ApplicationException
     * @throws Throwable
     */
    public function index()
    {
        return (new Grid(new SysAdPlace()))->setLists(ListSysAdPlace::class)->render();
    }

    /**
     * 创建/编辑广告位
     *
     * @return Factory|JsonResponse|RedirectResponse|Response|Redirector|View
     */
    public function establish()
    {
        return (new FormPlaceEstablish())->render();
    }

    /**
     * 删除广告位
     *
     * @param int $id 广告位ID
     *
     * @return JsonResponse|RedirectResponse|Response
     */
    public function delete(int $id)
    {
        $Place = new Place();
        if ($Place->delete($id)) {
            return Resp::success('删除成功', '_reload|1');
        }

        return Resp::error($Place->getError());
    }
}
