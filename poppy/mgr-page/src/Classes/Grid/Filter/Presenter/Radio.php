<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Presenter;

class Radio extends Presenter
{
    /**
     * @var array
     */
    protected array $options = [];

    /**
     * Display inline.
     *
     * @var bool
     */
    protected bool $inline = true;

    /**
     * Radio constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options =  $options;
        return $this;
    }

    /**
     * Draw stacked radios.
     *
     * @return $this
     */
    public function stacked(): self
    {
        $this->inline = false;
        return $this;
    }

    /**
     * @return array
     */
    public function variables(): array
    {
        return [
            'options' => $this->options,
            'inline'  => $this->inline,
        ];
    }
}
