<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Illuminate\Support\Arr;

class In extends FilterItem
{
    /**
     * {@inheritDoc}
     */
    protected string $query = 'whereIn';

    /**
     * Get condition of this filter.
     *
     * @return array|void
     */
    public function condition(array $inputs)
    {
        $value = Arr::get($inputs, $this->column);

        if (is_null($value)) {
            return;
        }

        $value       = is_string($value) ? explode(',', $value) : $value;

        $this->value = $value;

        return $this->buildCondition($this->column, $this->value);
    }
}
