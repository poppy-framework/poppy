<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchHidden extends ListSearchWhere
{
    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        $hidden = DemoGrid::inRandomOrder()->value('account_id');

        return function (Filter $filter) use ($hidden) {
            $filter->column(0, function (Filter $filter) use ($hidden) {
                $filter->hidden('account_id')->value($hidden);
            });
        };
    }
}
