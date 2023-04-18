<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

class EndsWith extends Like
{
    protected string $exprFormat = '%{value}';
}
