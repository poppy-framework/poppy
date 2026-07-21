<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class DatetimeRange extends Date
{
    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->options([
            'layui-range' => true,
            'layui-type'  => 'datetime',
        ]);
        $this->attribute('style', 'width:300px');

        return parent::render();
    }
}
