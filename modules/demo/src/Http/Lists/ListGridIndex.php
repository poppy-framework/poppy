<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\ListBase;

class ListGridIndex extends ListBase
{
    /**
     * {@inheritDoc}
     *
     * @throws ApplicationException
     */
    public function columns()
    {
        // 自定义样式
        $this->column('id', 'ID(排序)')->sortable()->width(100);

        // 开关
        $this->column('is_enable', 'Switch')->switch([
            1 => '打开',
            0 => '关闭',
        ]);

        // 文件链接地址
        $this->column('file', '文件链接地址')->link();

        $this->column('prefix', 'Prefix(默认前缀-Py)')->prefix('Py');

        $this->column('suffix', 'Suffix(默认后缀-Py)')->suffix('Py');
    }

    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
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
            $filter->column(2, function (Filter $filter) {
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
            $filter->column(1, function (Filter $filter) {
                $filter->notEqual('created_at')->datetime();
            });
            $filter->column(1, function (Filter $filter) {
                $filter->where(function ($query) {
                    $handle = input('handle');
                    $query->where('handle', 'like', "%{$handle}%");
                }, 'button', 'handle');
            });

            $filter->column(1, function (Filter $filter) {
                $filter->in('type')->multipleSelect(['user' => '用户', 'backend' => '管理员']);
            });
            $filter->column(1, function (Filter $filter) {
                $filter->notIn('op_group')->multipleSelect(['play' => '伙玩', 'dailian' => '代练', 'fadan' => '发单']);
            });
            $filter->column(1, function (Filter $filter) {
                $filter->endsWith('suffix');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->contains('modal');
            });
            $filter->column(0, function (Filter $filter) {
                $filter->hidden('id', '1');
            });
        };
    }
}
