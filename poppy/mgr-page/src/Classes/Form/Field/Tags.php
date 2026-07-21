<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Poppy\MgrPage\Classes\Form\Field;

class Tags extends Field
{
    /**
     * @var array
     */
    protected $value = [];

    /**
     * @var string
     */
    protected $visibleColumn;

    /**
     * @var string
     */
    protected $key;

    private int $max = 5;

    /**
     * @var true
     */
    private bool $create = false;

    /**
     * 设置最大值
     *
     * @return $this
     */
    public function max(int $num = 5): self
    {
        $this->max = $num;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function fill($data): void
    {
        $this->value = Arr::get($data, $this->column);

        if (is_array($this->value)) {
            $this->value = array_column($this->value, $this->visibleColumn, $this->key);
        }

        if (is_string($this->value)) {
            $this->value = explode(',', $this->value);
        }

        $this->value = array_filter((array) $this->value, 'strlen');
    }

    /**
     * Set the field options.
     *
     * @param array|Collection|Arrayable $options
     *
     * @return $this|Field
     */
    public function options($options = [])
    {
        if ($options instanceof Collection) {
            $options = $options->toArray();
        }
        $this->options = $options + $this->options;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function prepare($value)
    {
        $value = array_filter($value, 'strlen');

        if (!Arr::isAssoc($value)) {
            $value = implode(',', $value);
        }

        return $value;
    }

    /**
     * Get or set value for this field.
     *
     * @return $this|array|mixed
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            return empty($this->value) ? ($this->getDefault() ?? []) : $this->value;
        }

        $this->value = (array) $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->addVariables([
            'options' => $this->options,
            'create'  => $this->create,
            'max'     => $this->max,
        ]);

        return parent::render();
    }

    /**
     * 是否允许创建
     *
     * @return $this
     */
    public function create(): self
    {
        $this->create = true;

        return $this;
    }

    /**
     * Set visible column and key of data.
     *
     * @return $this
     */
    public function pluck($visibleColumn, $key)
    {
        $this->visibleColumn = $visibleColumn;
        $this->key           = $key;

        return $this;
    }
}
