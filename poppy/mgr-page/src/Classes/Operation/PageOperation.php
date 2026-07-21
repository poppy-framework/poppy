<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

/**
 * 跳转打开
 */
class PageOperation extends Operation
{
    public function render(): string
    {
        $this->classes[] = 'J_ignore';

        return parent::render();
    }
}
