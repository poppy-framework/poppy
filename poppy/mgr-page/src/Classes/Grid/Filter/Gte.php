<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Illuminate\Support\Arr;

class Gte extends FilterItem
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'py-mgr-page::tpl.filter.gte';

    /**
     * Get condition of this filter.
     *
     * @return array|mixed|void
     */
    public function condition(array $inputs)
    {
        $value = Arr::get($inputs, $this->column);

        if (is_null($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, '>=', $this->value);
    }
}
