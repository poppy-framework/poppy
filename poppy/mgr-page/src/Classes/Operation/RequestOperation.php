<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

/**
 * 请求操作
 */
final class RequestOperation extends Operation
{

    public function render(): string
    {
        $this->classes[] = 'J_request';
        return parent::render();
    }
}
