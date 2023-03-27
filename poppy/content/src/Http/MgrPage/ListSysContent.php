<?php
declare(strict_types = 1);

namespace Poppy\Content\Http\MgrPage;

use Closure;
use Poppy\Content\Models\SysContent;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;

class ListSysContent extends ListBase
{

    public $title = '分类管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('title', "标题");
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysContent $item */
            $item = $actions->row;
            $actions->edit(route('py-category:backend.category.establish', [$item->id]));
            $actions->delete(route('py-category:backend.category.delete', [$item->id]), $item->title);
        },])->width(170);
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $type = input(Scope::QUERY_NAME, SysContent::TYPE_DEFAULT);
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->like('title', '标题');
            });
            // todo 这里需要默认选择为空的上级 ID
            $filter->column(1 / 12, function (Filter $ft) use ($type) {
                $ft->equal('parent_id', '上级 ID')->select(SysContent::tree($type, true));
            });

            $types = SysContent::kvType();
            foreach ($types as $t => $v) {
                $filter->scope($t, $v)->where('type', $t);
            }
        };
    }

    public function quickButtons(): Closure
    {
        $scope = input(Scope::QUERY_NAME);
        return function (Operations $operations) use ($scope) {
            $operations->create(route_url('py-category:backend.category.establish', null, ['type' => $scope]), '新建类别');
        };
    }
}
