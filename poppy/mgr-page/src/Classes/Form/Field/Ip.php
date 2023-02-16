<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class Ip extends Text
{
    protected $rules = [
        'nullable', 'ip',
    ];

    /**
     * @see https://github.com/RobinHerbots/Inputmask#options
     *
     * @var array
     */
    protected $options = [
        'alias' => 'ip',
    ];

    public function render()
    {
        $this->defaultAttribute('style', 'width: 130px');

        return parent::render();
    }
}
