<?php

declare(strict_types = 1);

namespace Poppy\App\Http\Request\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\App\Action\App;
use Poppy\App\Http\MgrPage\FormAppEstablish;
use Poppy\App\Http\MgrPage\ListSysApp;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Throwable;

/**
 * 应用管理
 */
class AppController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        self::$permission = [
            'global' => 'backend:py-app.app.manage',
        ];
    }

    /**
     * 列表
     * @return JsonResponse|RedirectResponse|Response|string
     * @throws ApplicationException
     * @throws Throwable
     */
    public function index()
    {
        $grid = new Grid(new SysApp());
        $grid->setLists(ListSysApp::class);
        return $grid->render();
    }

    /**
     * Show the form for creating a new resource.
     * @throws Throwable
     */
    public function establish()
    {
        return (new FormAppEstablish())->render();
    }

    /**
     * 删除分类
     * @param int $id 分类ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function status(int $id, int $status)
    {
        $App = new App();
        $App->status($id, $status);
        return Resp::success('更改应用状态成功', '_reload|1');
    }
}
