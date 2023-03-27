<?php

declare(strict_types = 1);

namespace Poppy\Content\Http\Request\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\Content\Action\Content;
use Poppy\Content\Http\MgrPage\FormContentEstablish;
use Poppy\Content\Http\MgrPage\ListSysContent;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Classes\Operations;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Throwable;

/**
 * 内容管理
 */
class ContentController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        self::$permission = [
            'global' => 'backend:py-content.content.index',
        ];
    }

    /**
     * 分类列表
     * @return JsonResponse|RedirectResponse|Response|string
     * @throws ApplicationException
     * @throws Throwable
     */
    public function index()
    {
        return (new Grid(new SysContent()))->setLists(ListSysContent::class)->render();
    }

    /**
     * Show the form for creating a new resource.
     * @throws Throwable
     */
    public function establish()
    {
        return (new FormContentEstablish())->render();
    }

    /**
     * 删除分类
     * @param int $id 分类ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function delete(int $id)
    {
        $Content = $this->action();
        if ($Content->delete($id)) {
            return Resp::success('删除成功', '_reload|1');
        }

        return Resp::error($Content->getError());
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

    private function action(): Content
    {
        return (new Content());
    }
}
