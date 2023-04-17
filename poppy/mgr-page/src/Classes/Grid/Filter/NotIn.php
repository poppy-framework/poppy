<?php

namespace Poppy\MgrPage\Classes\Grid\Filter;

class NotIn extends In
{
    /**
     * @inheritDoc
     */
    protected string $query = 'whereNotIn';
}
