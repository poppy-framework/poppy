<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter;

class Hidden extends AbstractFilter
{

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected string $view = 'py-mgr-page::tpl.filter.hidden';


    public function value($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function condition(array $inputs)
    {

    }
}
