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
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Throwable;

/**
 * 分类管理
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
        $grid = new Grid(new SysContent());
        $grid->setLists(ListSysContent::class);
        return $grid->render();
    }

    /**
     * Show the form for creating a new resource.
     * @throws Throwable
     */
    public function establish()
    {
        return (new \Poppy\Ad\Http\MgrPage\FormContentEstablish())->render();
    }

    /**
     * 删除分类
     * @param int $id 分类ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function delete(int $id)
    {
        $Category = new Content();
        if ($Category->delete($id)) {
            return Resp::success('删除分类成功', '_reload|1');
        }

        return Resp::error($Category->getError());
    }
}
