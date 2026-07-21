<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\ListBase;

class ListGridEditable extends ListBase
{
    /**
     * {@inheritDoc}
     *
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', 'Id');
        $this->column('list_order', 'ListOrder')->editable()->sortable();
        $this->column('image', '图片(可放大)')->image('', 28, 28);
        $this->column('title', '标题(可复制)')->copyable();
        $this->column('progress')->progress();
        $this->column('loading')->loading(['N'], ['Y' => 'over']);
        $this->column('qr')->display(function () {
            return data_get($this, 'link');
        })->qrcode();

        $this->column('title-hide', '标题(隐藏)')->display(function () {
            return data_get($this, 'title');
        });
        $this->column('a');
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
                $filter->startsWith('title');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->day('day');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->date('date');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->year('year');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->month('month');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->group('group', 'Group', function (Filter\Group $group) {
                    // 等于
                    $group->equal('=');

                    // 不等于
                    $group->notEqual('!=');

                    // 大于
                    $group->gt('>');

                    // 小于
                    $group->lt('<');

                    // 大于等于
                    $group->nlt('>=');

                    // 小于等于
                    $group->ngt('<=');

                    // 匹配
                    $group->match('*');

                    // 复杂条件
                    // $group->where('啥', function($f){
                    //     $f;
                    // });

                    // like查询
                    $group->like('%');

                    // like查询
                    $group->contains('*');

                    // ilike查询
                    $group->ilike('like');

                    // 以输入的内容开头
                    $group->startWith('start');

                    // 以输入的内容结尾
                    $group->endWith('endwith');
                });
            });
            $filter->column(2, function (Filter $filter) {
                $filter->between('created_at')->datetime();
            });
        };
    }
}
