<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class BetweenDate extends FilterItem
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'py-mgr-page::tpl.filter.between_date';

    protected bool $withTime = false;

    protected array $variables = [
        'layui-range' => 'true',
        'layui-type'  => 'date',
    ];

    /**
     * Get condition of this filter.
     *
     * @return array|void
     */
    public function condition(array $inputs)
    {
        if (!Arr::has($inputs, $this->column)) {
            return;
        }

        $this->value = Arr::get($inputs, $this->column);
        if (!$this->value) {
            return;
        }
        [$start, $end] = explode(' - ', $this->value);

        if (!$this->withTime) {
            $start = Carbon::parse($start)->startOfDay()->toDateTimeString();
            $end   = Carbon::parse($end)->endOfDay()->toDateTimeString();
        }

        return $this->buildCondition([
            [$this->column, '<=', trim($end)],
            [$this->column, '>=', trim($start)],
        ]);
    }

    public function variables(): array
    {
        $variables = parent::variables();

        return array_merge($variables, ['variables' => $this->variables]);
    }

    public function withTime(): void
    {
        $this->withTime                = true;
        $this->variables['layui-type'] = 'datetime';
    }
}
