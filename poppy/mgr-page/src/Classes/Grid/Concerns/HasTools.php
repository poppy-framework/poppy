<?php

namespace Poppy\MgrPage\Classes\Grid\Concerns;

use Closure;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Classes\Grid\Tools;

trait HasTools
{
    use HasQuickSearch;

    /**
     * Header tools.
     *
     * @var Tools
     */
    public $tools;

    /**
     * 是否显示导出按钮
     */
    public function isShowTools(): bool
    {
        return $this->option('show_tools');
    }

    /**
     * Disable export.
     *
     * @return $this
     */
    public function disableTools(bool $disable = true): self
    {
        return $this->option('show_tools', !$disable);
    }

    /**
     * Setup grid tools.
     *
     * @return void
     */
    public function tools(Closure $callback): self
    {
        $callback($this->tools);

        return $this;
    }

    /**
     * Render custom tools.
     */
    public function renderHeaderTools(): string
    {
        return $this->tools->render();
    }

    /**
     * Setup grid tools.
     *
     * @return $this
     */
    protected function initTools(Grid $grid): self
    {
        $this->tools = new Tools($grid);

        return $this;
    }
}
