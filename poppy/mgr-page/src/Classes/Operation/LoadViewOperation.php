<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

/**
 * 加载Tab操作
 */
final class LoadViewOperation extends Operation
{

    public function render(): string
    {
        $this->classes[] = 'J_load_view';
        return parent::render();
    }
}
