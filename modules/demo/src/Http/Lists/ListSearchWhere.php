<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\Area\Classes\Grid\Filter\Presenter\Area;
use Poppy\Area\Models\SysArea;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\ListBase;

class ListSearchWhere extends ListBase
{
    /**
     * {@inheritDoc}
     *
     * @throws ApplicationException
     */
    public function columns(): void
    {
        $this->column('id', 'Id');
        $this->column('title', '标题');
        $this->column('area_id', '地区')->display(function ($area_id) {
            return SysArea::kvArea($area_id);
        });
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var DemoGrid $item */
            $item = $actions->row;
            $actions->edit(route('py-mgr-page:backend.pam.establish', [$item->id]));
        },])->fixed();
    }

    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->where(function ($query) {
                    $title = input('i_title');
                    $query->where('title', 'like', '%' . $title . '%');
                }, 'Where(标题存在)', 'i_title');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->where(function ($query) {
                    // 自定义地区的查找
                    $areaId   = input('area_id');
                    $children = SysArea::where('id', $areaId)->value('children');
                    $query->whereIn('area_id', explode(',', $children));
                }, '地区', 'area_id')->setPresenter(new Area());
            });
        };
    }
}
