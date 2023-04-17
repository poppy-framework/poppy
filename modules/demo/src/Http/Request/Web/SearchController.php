<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Demo\Http\Lists\ListPoppyDefault;
use Demo\Http\Lists\ListPoppyDemo;
use Demo\Http\Lists\ListPoppyEditable;
use Demo\Http\Lists\ListPoppyIndex;
use Demo\Http\Lists\ListPoppyOperation;
use Demo\Http\Lists\ListPoppyUser;
use Demo\Models\DemoWebapp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\System\Http\Request\Web\WebController;
use Throwable;

/**
 * 搜索
 */
class SearchController extends WebController
{

    /**
     * @throws Throwable
     * @throws ApplicationException
     */
    public function index($type)
    {
        // 第一列显示id字段，并将这一列设置为可排序列
        $grid = new Grid(new DemoWebapp());
        $grid->setTitle('Title');
        if ($type === 'demo') {
            $grid->setLists(ListPoppyDemo::class);
        }
        if ($type === 'edit') {
            $grid->setLists(ListPoppyEditable::class);
        }
        if ($type === 'index') {
            $grid->setLists(ListPoppyIndex::class);
        }
        if ($type === 'default') {
            $grid->setLists(ListPoppyDefault::class);
        }
        if ($type === 'user') {
            $grid->setLists(ListPoppyUser::class);
        }
        if ($type === 'operation') {
            $grid->setLists(ListPoppyOperation::class);
        }
        return $grid->render();
    }
}
