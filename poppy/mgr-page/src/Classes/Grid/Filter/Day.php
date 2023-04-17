<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

class Day extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    protected string $query = 'whereDay';
}
