<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Illuminate\Support\Arr;

class NotEqual extends FilterItem
{
    /**
     * {@inheritDoc}
     */
    public function condition(array $inputs)
    {
        $value = Arr::get($inputs, $this->column);

        if (!isset($value)) {
            return;
        }

        $this->value = $value;

        return $this->buildCondition($this->column, '!=', $this->value);
    }
}
