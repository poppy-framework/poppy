<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Displayer;

/**
 * Class Copyable.
 *
 * @see https://codepen.io/shaikmaqsood/pen/XmydxJ
 */
class Copyable extends AbstractDisplayer
{
    public function display(): string
    {
        return <<<HTML
<span data-text="{$this->getValue()}" class="J_copy" style="cursor: pointer;">
    <i class="bi bi-clipboard"></i> {$this->getValue()}
</span>&nbsp;
HTML;
    }
}
