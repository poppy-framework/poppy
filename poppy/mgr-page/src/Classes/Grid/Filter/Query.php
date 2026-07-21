<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

/**
 * 加入查询默认条件(例如用于个人和全部管理的数据混用)
 */
class Query extends FilterItem
{
    /**
     * @var string|int
     */
    protected $val = '';

    public function __construct($column, string $label = '')
    {
        parent::__construct($column, $label);
        $this->presenter = null;
    }

    public function value($value = ''): self
    {
        $this->val = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function condition(array $inputs)
    {
        return $this->buildCondition($this->column, $this->val);
    }
}
