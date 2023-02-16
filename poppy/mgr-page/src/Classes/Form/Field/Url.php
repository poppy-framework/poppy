<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class Url extends Text
{
    protected $rules = [
        'nullable', 'url',
    ];

    public function render()
    {
        $this->defaultAttribute('type', 'url');

        return parent::render();
    }
}
