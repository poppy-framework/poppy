<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchYear extends ListSearchWhere
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
        $this->column('score', '分数');
        $this->column('post_at', '公布时间');
        $this->column('birth_date', '生日');
        $this->column('status', '状态')->using(DemoGrid::kvStatus());
    }

    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->year('birth_date', '年(出生)');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->year('post_at', '年(发布时间)');
            });
        };
    }
}
