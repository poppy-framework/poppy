<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class Decimal extends Text
{
    public function render()
    {
        $this->defaultAttribute('style', 'width: 130px');

        $this->addVariables([
            'type' => 'number',
        ]);

        return parent::render();
    }
}
