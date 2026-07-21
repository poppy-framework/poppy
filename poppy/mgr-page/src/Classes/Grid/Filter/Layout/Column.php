<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Layout;

use Illuminate\Support\Collection;
use Poppy\MgrPage\Classes\Grid\Filter\FilterItem;

class Column
{
    /**
     * @var Collection|FilterItem[]
     */
    protected Collection $filters;

    protected int $width;

    /**
     * Column constructor.
     */
    public function __construct(int $width = 12)
    {
        $this->width   = $width;
        $this->filters = new Collection();
    }

    /**
     * Add a filter item to this column.
     */
    public function addFilter(FilterItem $filter): void
    {
        $this->filters->push($filter);
    }

    /**
     * Get all filters in this column.
     *
     * @return Collection|FilterItem[]
     */
    public function filters(): Collection
    {
        return $this->filters;
    }

    /**
     * 过滤器数量
     */
    public function filterCount(): int
    {
        $count = 0;
        foreach ($this->filters as $filter) {
            if ($filter->isRender()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Set column width.
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get column width.
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * Remove filter from column by id.
     */
    public function removeFilterByID(string $id)
    {
        $this->filters = $this->filters->reject(function (FilterItem $filter) use ($id) {
            return $filter->getId() === $id;
        });
    }
}
