<?php
declare(strict_types = 1);

namespace Poppy\Content\Http\MgrPage;

use Closure;
use Poppy\Category\Models\SysCategory;
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

    public $title = '内容管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns(): void
    {
        $this->column('id', 'ID')->sortable()->width(80);
        $this->column('list_order', '排序')->editable()->width(80)->sortable();
        $this->column('thumb', '缩略图')->image()->width(80);

        if (app('poppy')->exists('poppy.category')) {
            $this->column('cat_id', '分类')->display(function ($value) {
                if (!$value) {
                    return '-';
                }
                return SysCategory::kvTitle($value);
            })->width(140);
        }

        $this->column('title', '标题');
        $this->column('author', '发布者')->widthAsIp();
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysContent $item */
            $item = $actions->row;
            $actions->loadView('编辑', route('py-content:backend.content.establish', [$item->id]))->icon('pen')->primary();
            $actions->delete(route('py-content:backend.content.delete', [$item->id]), $item->title);
            if ($item->is_enable) {
                $actions->disable(route('py-content:backend.content.toggle', [$item->id]), $item->title, '展示');
            }
            else {
                $actions->enable(route('py-content:backend.content.toggle', [$item->id]), $item->title, '隐藏');
            }
        },])->width(215);
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->like('title', '标题');
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
            $operations->loadView('新建文章', route_url('py-content:backend.content.establish', null, ['type' => $scope]))
                ->icon('plus-circle')->sm();
        };
    }
}
