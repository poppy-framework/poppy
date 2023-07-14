<?php
declare(strict_types = 1);

namespace Poppy\Category\Http\MgrPage;

use Closure;
use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;

class ListSysCategory extends ListBase
{

    public $title = '分类管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns(): void
    {
        $this->column('id', 'ID')->sortable()->width(80);
        $this->column('list_order', '排序')->editable()->sortable()->width(80);
        $this->column('name', '标识')->display(function ($value) {
            /** @var $this SysCategory */
            return $value ? $this->type . '-' . $value : '';
        })->width(150);
        $this->column('title', '标题');
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysCategory $item */
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
            $type = input(Scope::QUERY_NAME, SysCategory::TYPE_DEFAULT);
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->like('title', '标题');
            });
            $filter->column(1 / 12, function (Filter $ft) use ($type) {
                $ft->equal('parent_id', '上级 ID')->select(SysCategory::tree($type, true));
            });
            $types = SysCategory::kvType();
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
