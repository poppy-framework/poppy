<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

/**
 * 复制
 */
final class CopyOperation extends Operation
{

    protected string $renderType = 'tag';

    private string $content;

    public function __construct($title, $content)
    {
        parent::__construct($title, '');
        $this->content = $content;
        $this->icon    = 'clipboard';
    }


    public function render(): string
    {
        $this->classes[] = 'J_copy';
        $this->classes[] = 'cursor-pointer';

        $this->attributes['data-text'] = $this->content;
        return parent::render();
    }
}
