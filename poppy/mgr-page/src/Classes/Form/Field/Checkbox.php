<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Checkbox extends MultipleSelect
{
    /**
     * 是否行内显示
     * @var bool
     */
    protected bool $inline = true;


    /**
     * @inheritdoc
     */
    protected $default = [];

    /**
     * 是否可以全选
     * @var bool
     */
    protected bool $canCheckAll = false;

    /**
     * @inheritDoc
     */
    public function fill($data): void
    {
        $value       = Arr::get($data, $this->column);
        $this->value = is_null($value) ? $this->default : $value;
        $this->value = (array) $this->value;
    }

    /**
     * Set options.
     *
     * @param array|callable|string $options
     *
     * @return $this
     */
    public function options($options = []): self
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        if (is_callable($options)) {
            $this->options = $options;
        }
        else {
            $this->options = (array) $options;
        }

        return $this;
    }

    /**
     * 默认值, 当没有数据做填充的时候会取这个默认值(null 值的时候)
     * @param array|callable|string $default
     * @return $this
     */
    public function default($default): self
    {
        $this->default = (array) $default;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->addVariables([
            'inline'      => $this->inline,
            'canCheckAll' => $this->canCheckAll,
        ]);
        return parent::render();
    }

    /**
     * Add a checkbox above this component, so you can select all checkboxes by click on it.
     *
     * @return $this
     */
    public function canCheckAll(): self
    {
        $this->canCheckAll = true;
        return $this;
    }

    /**
     * Draw inline checkboxes.
     *
     * @return $this
     */
    public function inline(): self
    {
        $this->inline = true;
        return $this;
    }

    /**
     * Draw stacked checkboxes.
     *
     * @return $this
     */
    public function stacked(): self
    {
        $this->inline = false;
        return $this;
    }
}
