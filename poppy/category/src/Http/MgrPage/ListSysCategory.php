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
use Poppy\MgrPage\Classes\Grid\Tools\BaseButton;

class ListSysCategory extends ListBase
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
            // todo 这里需要默认选择为空的上级 ID
            $filter->column(1 / 12, function (Filter $ft) use ($type) {
                $ft->equal('parent_id', '上级 ID')->select(SysCategory::tree($type, true));
            });

            $types = SysCategory::kvType();
            foreach ($types as $t => $v) {
                $filter->scope($t, $v)->where('type', $t);
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function actions()
    {
        $this->addColumn(Column::NAME_ACTION, '操作')
            ->displayUsing(Actions::class, [
                function (Actions $actions) {
                    /** @var SysCategory $item */
                    $item = $actions->row;
                    $actions->append([
                        new BaseButton('<i class="fa fa-edit"></i>', route('py-category:backend.category.establish', [$item->id]), [
                            'title' => "编辑 [{$item->title}]",
                            'class' => 'J_iframe',
                        ]),
                        new BaseButton('<i class="fa fa-trash-alt text-danger"></i>', route('py-category:backend.category.delete', [$item->id]), [
                            'title'        => "删除 [{$item->title}]",
                            'data-confirm' => "确定要删除 [{$item->title}]",
                            'class'        => 'J_request',
                        ]),
                    ]);
                },
            ]);
    }


    public function quickButtons(): array
    {
        $scope = input(Scope::QUERY_NAME);
        return [
            new BaseButton('<i class="fa fa-plus"></i> 新增', route_url('py-category:backend.category.establish', null, ['type' => $scope]), [
                'title' => "新增类别",
                'class' => 'J_iframe layui-btn layui-btn-sm',
            ]),
        ];
    }
}
