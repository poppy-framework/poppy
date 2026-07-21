<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;

class ListSearchWith extends ListSearchWhere
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
        $this->column('truename', '姓名');
    }

    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->startsWith('title', 'Title(前缀)');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->endsWith('truename', 'Truename(后缀)');
            });
        };
    }
}
