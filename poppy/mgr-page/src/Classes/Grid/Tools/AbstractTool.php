<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Tools;

use Illuminate\Contracts\Support\Renderable;
use Poppy\MgrPage\Classes\Grid;

abstract class AbstractTool implements Renderable
{
    /**
     * @var Grid
     */
    protected Grid $grid;

    /**
     * @var bool
     */
    protected bool $disabled = false;

    /**
     * Toggle this button.
     *
     * @param bool $disable
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

    /**
     * @return Grid
     */
    public function getGrid(): Grid
    {
        return $this->grid;
    }

    /**
     * Set parent grid.
     *
     * @param Grid $grid
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
