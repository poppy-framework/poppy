<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class Time extends Date
{
    protected $options = [
        'layui-type' => 'time',
    ];
}
