<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class Month extends Date
{
    /**
     * @inheritDoc
     */
    protected string $query = 'whereBetween';


    protected string $fieldName = 'month';

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return array|void
     */
    public function condition(array $inputs)
    {
        $value = Arr::get($inputs, $this->column);

        if (is_null($value)) {
            return;
        }

        $this->value = $value;

        $carbon = Carbon::parse($value);

        return $this->buildCondition($this->column, [
            $carbon->startOfMonth()->toDateTimeString(), $carbon->endOfMonth()->toDateTimeString(),
        ]);
    }
}
