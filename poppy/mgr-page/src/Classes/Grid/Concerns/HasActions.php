<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Concerns;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Classes\Operations;

trait HasActions
{
    /**
     * Callback for grid actions.
     *
     * @var Closure
     */
    protected $actionsCallback;

    /**
     * Actions column display class.
     *
     * @var string
     */
    protected $actionsClass;


    /**
     * @var array
     */
    protected array $batchActions = [];

    protected ?Operations $batchOperations = null;

    /**
     * Set grid action callback.
     *
     * @param Closure|string $actions
     *
     * @return $this
     */
    public function actions($actions)
    {
        if ($actions instanceof Closure) {
            $this->actionsCallback = $actions;
        }

        return $this;
    }

    /**
     * Get action display class.
     *
     * @return string
     */
    public function getActionClass()
    {
        if ($this->actionsClass) {
            return $this->actionsClass;
        }

        return \Poppy\MgrPage\Classes\Grid\Displayer\Actions::class;
    }

    /**
     * @param string $actionClass
     *
     * @return $this
     */
    public function setActionClass(string $actionClass)
    {
        if (is_subclass_of($actionClass, \Poppy\MgrPage\Classes\Grid\Displayer\Actions::class)) {
            $this->actionsClass = $actionClass;
        }

        return $this;
    }

    /**
     * Set grid batch-action callback.
     *
     *
     * @return $this
     */
    public function batchActions($buttons): self
    {
        if (is_array($buttons) && count($buttons)) {
            foreach ($buttons as $button) {
                if (!($button instanceof Renderable)) {
                    continue;
                }
                $this->batchActions[] = $button;
            }
        }

        if ($buttons instanceof Closure) {
            $operations = new Operations();
            $buttons($operations);
            $this->batchOperations = $operations;
        }
        return $this;
    }

    /**
     * @param bool $disable
     *
     * @return Grid|mixed
     */
    public function disableBatchActions(bool $disable = true)
    {
        $this->tools->disableBatchActions($disable);

        return $this->option('show_row_selector', !$disable);
    }


    /**
     * Render create button for grid.
     *
     * @return string
     */
    public function renderBatchActions(): string
    {
        $append = '';
        if (count($this->batchActions)) {
            foreach ($this->batchActions as $button) {
                $append .= $button->render();
            }
        }
        if ($this->batchOperations) {
            $append .= $this->batchOperations->render();
        }
        return $append;
    }

    /**
     * Render create button for grid.
     *
     * @return string
     */
    public function skeletonBatchActions(): string
    {
        $append = '';
        foreach ($this->batchActions as $button) {
            $append .= $button->render();
        }
        return $append;
    }
}
