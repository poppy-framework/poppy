<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

class Year extends Date
{
    /**
     * @inheritDoc
     */
    protected string $query = 'whereYear';

    /**
     * @var string
     */
    protected string $fieldName = 'year';
}
