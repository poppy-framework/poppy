<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Tools;

use Illuminate\Contracts\Support\Renderable;
use Poppy\MgrPage\Classes\Grid;

abstract class AbstractTool implements Renderable
{
    protected Grid $grid;

    protected bool $disabled = false;

    /**
     * Toggle this button.
     *
     * @return $this
     */
    public function disable(bool $disable = true): self
    {
        $this->disabled = $disable;

        return $this;
    }

    /**
     * If the tool is allowed.
     */
    public function allowed(): bool
    {
        return !$this->disabled;
    }

    public function getGrid(): Grid
    {
        return $this->grid;
    }

    /**
     * Set parent grid.
     *
     * @return $this
     */
    public function setGrid(Grid $grid): self
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
