<?php

declare(strict_types = 1);

namespace Poppy\Area\Classes\Grid\Filter;

use Poppy\MgrPage\Classes\Grid\Filter\FilterItem;

class Area extends FilterItem
{

    public function render()
    {
        $this->presenter = new Presenter\Area();
        return parent::render();
    }

}
