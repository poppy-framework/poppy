<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchQuery extends ListSearchWhere
{
    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        $hidden = DemoGrid::inRandomOrder()->value('account_id');
        return function (Filter $filter) use ($hidden) {
            $filter->query('account_id')->value($hidden);
        };
    }
}
