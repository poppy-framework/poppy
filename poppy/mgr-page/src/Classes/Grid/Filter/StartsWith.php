<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

class StartsWith extends Like
{
    protected string $exprFormat = '{value}%';
}
