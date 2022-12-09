<?php

namespace Poppy\MgrPage\Classes\Grid\Concerns;

use Poppy\MgrPage\Classes\Grid\Tools\BaseButton;

trait HasQuickButton
{
    /**
     * 快捷操作
     * @var array
     */
    protected array $quickButtons = [];

    /**
     * Get create url.
     *
     * @param BaseButton[]|BaseButton $buttons
     * @return array
     */
    public function appendQuickButton(array $buttons): array
    {

        if (count($buttons)) {
            foreach ($buttons as $button) {
                if (!($button instanceof BaseButton)) {
                    continue;
                }
                $this->quickButtons[] = $button;
            }
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
        $append = '';
        foreach ($this->quickButtons as $quickButton) {
            $append .= $quickButton->render();
        }
        return $append;
    }

    public function skeletonQuickButton(): array
    {
        $append = [];
        foreach ($this->quickButtons as $quickButton) {
            $append[] = $quickButton->renderSkeleton();
        }
        return $append;
    }
}
