<?php

declare(strict_types = 1);

namespace Demo\Http\Request\Web;

use Demo\Classes\DemoDef;
use Demo\Models\DemoGrid;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\System\Http\Request\Web\WebController;
use Poppy\System\Models\PamRole;
use Throwable;

/**
 * 内容生成器
 */
class GridController extends WebController
{
    /**
     * @throws Throwable
     * @throws ApplicationException
     */
    public function index($type)
    {
        return (new Grid(new DemoGrid()))->setTitle('Title')
            ->setLists('\Demo\Http\Lists\ListGrid' . ucfirst($type))
            ->render();
    }

    /**
     * @throws Throwable
     */
    public function noFile()
    {
        $grid = new Grid(new PamRole());
        $grid->setTitle('测试:无文件');
        // 自定义样式
        $grid->column('id', 'ID(排序)')->sortable()->width(100);
        $grid->column('title', '角色');
        // todo [3] nofile 显示的 action 不正确
        $grid->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            $item = $actions->row;
            $actions->iframe('编辑', DemoDef::IFRAME_INBOX_NONE)->icon('plus');
        },])->fixed()->width(120);

        return $grid->render();
    }

    public function iframe()
    {
        dump(input());
    }
}
