<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

/**
 * 网页预览(Iframe)
 */
final class IframeOperation extends Operation
{

    protected int $width = 550;

    /**
     * 预览
     * @param int $width 宽度
     * @return void
     */
    public function width(int $width = 550): self
    {
        $this->width = $width;
        return $this;
    }


    public function normal(): self
    {
        return $this->width(700);
    }

    public function large(): self
    {
        return $this->width(850);
    }


    public function render(): string
    {
        $this->classes[] = 'J_iframe';
        if ($this->width) {
            $this->attributes['data-width'] = $this->width;
        }
        return parent::render();
    }
}
