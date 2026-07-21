<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\System\Models\SysConfig;

class ListSearchEqual extends ListSearchWhere
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
        $this->column('status', '状态')->using(DemoGrid::kvStatus());
        $this->column('is_enable', '是否启用')->using(SysConfig::kvYn());
    }

    /**
     * {@inheritDoc}
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->equal('score', '分数');
            });
            $filter->column(1, function (Filter $filter) {
                $filter->equal('status', '状态')->select(DemoGrid::kvStatus());
            });
            $filter->column(1, function (Filter $filter) {
                $filter->equal('is_enable', '状态')->radio(SysConfig::kvYn());
            });
        };
    }
}
