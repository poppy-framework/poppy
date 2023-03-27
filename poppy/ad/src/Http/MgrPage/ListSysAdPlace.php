<?php


declare(strict_types = 1);

namespace Poppy\Ad\Http\MgrPage;

use Closure;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;

class ListSysAdPlace extends ListBase
{

    public $title = '位置管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('title', "标题")->width(130);
        $this->column('size', "尺寸")->width(100)->display(function () {
            /** @var SysAdPlace $this */
            return "{$this->width}x{$this->height}";
        });
        $this->column('thumb', "示意图")->width(100)->image();
        $this->column('introduce', "说明");

        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysAdPlace $item */
            $item = $actions->row;
            $actions->page('列表', route_url('py-ad:backend.content.index', null, ['place_id' => $item->id]))
                ->icon('grid');
            $actions->iframe('添加', route_url('py-ad:backend.content.establish', null, ['place_id' => $item->id]))
                ->icon('plus-circle');
            $actions->edit(route_url('py-ad:backend.place.establish', [$item->id]));
            $actions->delete(route_url('py-ad:backend.place.delete', [$item->id]), $item->title);
        },])->fixed()->width(255);
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->like('title', '名称');
            });
        };
    }

    public function quickButtons(): Closure
    {
        return function (Operations $operations) {
            $operations->create(route_url('py-ad:backend.place.establish'), '新增位置');
        };
    }
}
