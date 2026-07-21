<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Illuminate\Support\Collection;
use Poppy\MgrPage\Classes\Grid\Filter\Presenter\DateTime as DatetimePresenter;

class Date extends FilterItem
{
    /**
     * {@inheritDoc}
     */
    protected string $query = 'whereDate';

    protected string $fieldName = 'date';

    /**
     * {@inheritDoc}
     */
    public function __construct($column, $label = '')
    {
        parent::__construct($column, $label);

        $this->{$this->fieldName}();
    }

    /**
     * Date filter.
     */
    protected function date(): DatetimePresenter
    {
        return $this->datetime(['layui-type' => 'date']);
    }

    /**
     * Month filter.
     */
    protected function month(): DatetimePresenter
    {
        return $this->datetime(['layui-type' => 'month']);
    }

    /**
     * Year filter.
     */
    protected function year(): DatetimePresenter
    {
        return $this->datetime(['layui-type' => 'year']);
    }

    /**
     * Datetime filter.
     *
     * @param array|Collection $options
     */
    private function datetime($options = []): DatetimePresenter
    {
        return $this->setPresenter(new DatetimePresenter($options));
    }
}
