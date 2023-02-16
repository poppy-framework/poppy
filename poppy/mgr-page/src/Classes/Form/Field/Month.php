<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

class Month extends Date
{
    protected $options = [
        'layui-type' => 'month',
    ];
}
