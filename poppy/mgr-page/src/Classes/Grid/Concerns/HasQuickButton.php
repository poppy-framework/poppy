<?php

namespace Poppy\MgrPage\Classes\Grid\Concerns;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Poppy\MgrPage\Classes\Operations;

trait HasQuickButton
{
    /**
     * 快捷操作
     * @var array
     */
    protected array $quickButtons = [];

    protected ?Operations $operations = null;

    /**
     * 是否显示导出按钮
     */
    public function isShowQuickButton(): bool
    {
        return $this->option('show_quick_button');
    }

    /**
     * Disable export.
     *
     * @param bool $disable
     * @return $this
     */
    public function disableQuickButton(bool $disable = true): self
    {
        return $this->option('show_quick_button', !$disable);
    }

    /**
     * Get create url.
     *
     * @param array|Closure $buttons
     * @return array
     */
    public function appendQuickButton($buttons): array
    {
        if (is_array($buttons) && count($buttons)) {
            foreach ($buttons as $button) {
                if (!($button instanceof Renderable)) {
                    continue;
                }
                $this->quickButtons[] = $button;
            }
        }

        if ($buttons instanceof Closure) {
            $operations = new Operations();
            $buttons($operations);
            $this->operations = $operations;
        }
        return $this->quickButtons;
    }

    /**
     * Render create button for grid.
     *
     * @return string
     */
    public function renderQuickButton(): string
    {
        if (count($this->quickButtons)) {
            $append = '';
            foreach ($this->quickButtons as $quickButton) {
                $append .= $quickButton->render();
            }
            return $append;
        }
        if ($this->operations instanceof Operations) {
            return $this->operations->render();
        }
        return '';
    }
}
