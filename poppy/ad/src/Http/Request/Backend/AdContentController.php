<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http\Request\Backend;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Poppy\Ad\Action\Ad;
use Poppy\Ad\Http\MgrPage\FormContentEstablish;
use Poppy\Ad\Http\MgrPage\ListSysAdContent;
use Poppy\Ad\Models\SysAdContent;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;

/**
 * 广告管理
 */
class AdContentController extends BackendController
{

    public function __construct()
    {
        parent::__construct();
        self::$permission = [
            'global' => 'backend:py-ad.place.manage',
        ];
    }

    /**
     * 广告列表
     * @return JsonResponse|RedirectResponse|Response|string
     * @throws ApplicationException
     * @throws \Throwable
     */
    public function index()
    {
        return (new Grid(new SysAdContent()))->setLists(ListSysAdContent::class)->render();
    }

    /**
     * 创建/编辑广告
     * @return Factory|JsonResponse|RedirectResponse|Response|Redirector|View
     */
    public function establish()
    {
        return (new FormContentEstablish())->render();
    }

    /**
     * 删除广告
     * @param int $id 广告ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function delete(int $id)
    {
        $Place = $this->action();
        if ($Place->delete($id)) {
            return Resp::success('删除广告成功', '_reload|1');
        }

        return Resp::error($Place->getError());
    }

    /**
     * 开启/关闭 广告
     * @param int $id 活动ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function toggle(int $id)
    {
        $Ad = $this->action();
        if ($Ad->toggle($id)) {
            return Resp::success('操作成功', '_reload|1');
        }

        return Resp::error($Ad->getError());
    }

    /**
     * 广告Action
     * @return Ad()
     */
    private function action(): Ad
    {
        return (new Ad())->setPam($this->pam);
    }
}
