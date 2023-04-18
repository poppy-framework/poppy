<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchMonth extends ListSearchWhere
{

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns(): void
    {
        $this->column('id', 'Id');
        $this->column('title', '标题');
        $this->column('score', '分数');
        $this->column('post_at', '公布时间');
        $this->column('birth_date', '公布时间');
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->month('post_at', '月(发布时间)');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->month('birth_date', '月(出生日期)');
            });
        };
    }
}
