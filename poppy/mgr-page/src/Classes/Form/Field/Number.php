<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Poppy\Framework\Validation\Rule;

class Number extends Text
{

    protected string $type = 'number';


    public function __construct($column = '', $arguments = [])
    {
        parent::__construct($column, $arguments);
        $this->rules[] = Rule::numeric();
    }


    public function render()
    {
        $this->default($this->default);

        $this->defaultAttribute('style', 'width: 100px');

        return parent::render();
    }
}
