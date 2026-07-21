<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class Password extends Text
{
    public function render()
    {
        $this->addVariables([
            'type' => 'password',
        ]);
        $this->defaultAttribute('autocomplete', 'new-password');

        return parent::render();
    }
}
