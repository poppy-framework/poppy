<?php

declare(strict_types = 1);

namespace Poppy\Category\Http\Request\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\Category\Action\Category;
use Poppy\Category\Http\MgrPage\FormCategoryEstablish;
use Poppy\Category\Http\MgrPage\ListSysCategory;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Throwable;

/**
 * 分类管理
 */
class CategoryController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        self::$permission = [
            'global' => 'backend:py-category.category.index',
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
        $grid = new Grid(new SysCategory());
        $grid->setLists(ListSysCategory::class);
        return $grid->render();
    }

    /**
     * Show the form for creating a new resource.
     * @throws Throwable
     */
    public function establish()
    {
        $form = new FormCategoryEstablish();
        return $form->render();
    }

    /**
     * 删除分类
     * @param int $id 分类ID
     * @return JsonResponse|RedirectResponse|Response
     */
    public function delete(int $id)
    {
        $Category = new Category();
        if ($Category->delete($id)) {
            return Resp::success('删除分类成功', '_reload|1');
        }

        return Resp::error($Category->getError());
    }
}
