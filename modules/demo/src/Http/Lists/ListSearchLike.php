<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchLike extends ListSearchWhere
{
    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->like('title', 'Like(title)');
            });
        };
    }
}
