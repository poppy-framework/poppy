<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchBetween extends ListSearchWhere
{

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns(): void
    {
        $this->column('id', 'Id');
        $this->column('score', '分数');
        $this->column('progress', '进度')->progress();
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(2, function (Filter $filter) {
                $filter->between('score', '分数');
            });
            $filter->column(2, function (Filter $filter) {
                $filter->between('progress', '进度');
            });
        };
    }
}
