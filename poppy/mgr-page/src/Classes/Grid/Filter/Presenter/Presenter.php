<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Presenter;

use Poppy\MgrPage\Classes\Grid\Filter\FilterItem;
use ReflectionClass;

/**
 * 表现
 */
abstract class Presenter
{
    /**
     * @var FilterItem
     */
    protected $filter;

    /**
     * Set parent filter.
     */
    public function setParent(FilterItem $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @see https://stackoverflow.com/questions/19901850/how-do-i-get-an-objects-unqualified-short-class-name
     */
    public function view(): string
    {
        $reflect = new ReflectionClass(static::class);

        return 'py-mgr-page::tpl.filter.' . strtolower($reflect->getShortName());
    }

    public function type(): string
    {
        $reflect = new ReflectionClass(static::class);

        return strtolower($reflect->getShortName());
    }

    /**
     * Set default value for filter.
     *
     * @return $this
     */
    public function default($default): self
    {
        $this->filter->default($default);

        return $this;
    }

    /**
     * Blade template variables for this presenter.
     */
    public function variables(): array
    {
        return [];
    }
}
