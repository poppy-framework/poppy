<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Models\DemoGrid;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\System\Models\SysConfig;

class ListSearchIn extends ListSearchWhere
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
        $this->column('status', '状态')->using(DemoGrid::kvStatus());
        $this->column('is_enable', '是否启用')->using(SysConfig::kvYn());
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1, function (Filter $filter) {
                $filter->in('status', '状态')->multipleSelect(DemoGrid::kvStatus());
            });
        };
    }
}
