<?php


declare(strict_types = 1);

namespace Poppy\Ad\Http\MgrPage;

use Closure;
use Poppy\Ad\Models\SysAdContent;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;

class ListSysAdContent extends ListBase
{

    public $title = '内容管理';


    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('title', "标题");
        $this->column('list_order', "排序")->width(130);
        $this->column('src', "图片")->width(130)->image();
        $this->column('start_at', "展示开始时间")->widthAsDatetime();
        $this->column('end_at', "展示结束时间")->widthAsDatetime();

        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysAdContent $item */
            $item = $actions->row;
            $actions->edit(route_url('py-ad:backend.content.establish', [$item->id]));
            if ($item->is_enable) {
                $actions->disable(route_url('py-ad:backend.content.toggle', [$item->id]), $item->title);
            }
            else {
                $actions->enable(route_url('py-ad:backend.content.toggle', [$item->id]), $item->title);
            }
            $actions->delete(route_url('py-ad:backend.content.delete', [$item->id]), $item->title);
        },])->fixed()->width(255);
    }

    public function quickButtons(): Closure
    {
        return function (Operations $operations) {
            $operations->page('返回', route_url('py-ad:backend.place.index'))->sm()->normal()->icon('arrow-return-left');
        };
    }
}
