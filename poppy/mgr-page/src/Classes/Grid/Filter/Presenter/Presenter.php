<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Presenter;

use Poppy\MgrPage\Classes\Grid\Filter\AbstractFilter;
use ReflectionClass;

/**
 * 表现
 */
abstract class Presenter
{
    /**
     * @var AbstractFilter
     */
    protected $filter;

    /**
     * Set parent filter.
     *
     * @param AbstractFilter $filter
     */
    public function setParent(AbstractFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @see https://stackoverflow.com/questions/19901850/how-do-i-get-an-objects-unqualified-short-class-name
     *
     * @return string
     */
    public function view(): string
    {
        $reflect = new ReflectionClass(static::class);

        return 'py-mgr-page::tpl.filter.' . strtolower($reflect->getShortName());
    }

    /**
     */
    public function type(): string
    {
        $reflect = new ReflectionClass(static::class);
        return strtolower($reflect->getShortName());
    }

    /**
     * Set default value for filter.
     *
     * @param $default
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
     *
     * @return array
     */
    public function variables(): array
    {
        return [];
    }
}
