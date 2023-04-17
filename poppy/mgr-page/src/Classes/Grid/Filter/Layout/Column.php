<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Layout;

use Illuminate\Support\Collection;
use Poppy\MgrPage\Classes\Grid\Filter\AbstractFilter;

class Column
{
    /**
     * @var Collection|AbstractFilter[]
     */
    protected Collection $filters;

    /**
     * @var int
     */
    protected int $width;

    /**
     * Column constructor.
     *
     * @param int $width
     */
    public function __construct(int $width = 12)
    {
        $this->width   = $width;
        $this->filters = new Collection();
    }

    /**
     * Add a filter item to this column.
     * @param AbstractFilter $filter
     */
    public function addFilter(AbstractFilter $filter): void
    {
        $this->filters->push($filter);
    }

    /**
     * Get all filters in this column.
     *
     * @return Collection|AbstractFilter[]
     */
    public function filters(): Collection
    {
        return $this->filters;
    }


    /**
     * 过滤器数量
     * @return int
     */
    public function filterCount(): int
    {
        $count = 0;
        foreach ($this->filters as $filter) {
            if ($filter->isRender()) {
                $count += 1;
            }
        }
        return $count;
    }

    /**
     * Set column width.
     * @param int $width
     * @return Column
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Get column width.
     *
     * @return int
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
        $this->filters = $this->filters->reject(function (AbstractFilter $filter) use ($id) {
            return $filter->getId() === $id;
        });
    }
}
