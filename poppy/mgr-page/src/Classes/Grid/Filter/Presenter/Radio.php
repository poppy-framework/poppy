<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Presenter;

class Radio extends Presenter
{
    protected array $options = [];

    /**
     * Display inline.
     */
    protected bool $inline = true;

    /**
     * Radio constructor.
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

    public function variables(): array
    {
        return [
            'options' => $this->options,
            'inline'  => $this->inline,
        ];
    }
}
