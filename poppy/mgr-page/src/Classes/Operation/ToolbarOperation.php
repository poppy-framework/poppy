<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

use Illuminate\Support\Str;

/**
 * 网页预览(Iframe)
 */
final class ToolbarOperation extends Operation
{

    protected string $renderType = 'button';

    public function render(): string
    {
        $this->attributes['data-url']  = $this->url;
        $this->attributes['lay-event'] = Str::random(4);
        $this->classes[]               = 'J_request';
        return parent::render();
    }
}
