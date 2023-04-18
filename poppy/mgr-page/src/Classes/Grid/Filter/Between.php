<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Illuminate\Support\Arr;

class Between extends FilterItem
{
    /**
     * @inheritDoc
     */
    protected string $view = 'py-mgr-page::tpl.filter.between';

    /**
     * Format id.
     */
    public function formatId(string $column): array
    {
        $id = str_replace('.', '_', $column);
        return ['start' => "{$id}_start", 'end' => "{$id}_end"];
    }

    /**
     * Get condition of this filter.
     *
     * @param array $inputs
     *
     * @return void|array
     */
    public function condition(array $inputs)
    {
        if (!Arr::has($inputs, $this->column)) {
            return;
        }

        $this->value = Arr::get($inputs, $this->column);

        $value = array_filter($this->value, function ($val) {
            return $val !== '';
        });

        if (empty($value)) {
            return;
        }

        if (!isset($value['start'])) {
            return $this->buildCondition($this->column, '<=', $value['end']);
        }

        if (!isset($value['end'])) {
            return $this->buildCondition($this->column, '>=', $value['start']);
        }

        $this->query = 'whereBetween';

        return $this->buildCondition($this->column, $this->value);
    }

    /**
     * Format two field names of this filter.
     *
     * @param string $column
     *
     * @return array
     */
    protected function formatName($column): array
    {
        $columns = (array) explode('.', $column);
        if (count($columns) === 1) {
            $name = $columns[0];
        }
        else {
            $name = array_shift($columns);
            foreach ($columns as $col) {
                $name .= "[$col]";
            }
        }

        return ['start' => "{$name}[start]", 'end' => "{$name}[end]"];
    }
}
