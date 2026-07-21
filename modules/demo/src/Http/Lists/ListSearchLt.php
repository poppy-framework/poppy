<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchLt extends ListSearchGt
{
    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->lt('score', '分数');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->lte('progress', '进度');
            });
        };
    }
}
