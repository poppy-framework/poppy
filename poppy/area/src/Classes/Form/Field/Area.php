<?php

declare(strict_types = 1);

namespace Poppy\Area\Classes\Form\Field;

use Poppy\Area\Models\SysArea;
use Poppy\MgrPage\Classes\Form\Field;

final class Area extends Field
{
    /**
     * @inheritDoc
     */
    protected string $view = 'py-area::tpl.form.area';

    public function render()
    {
        $this->addVariables([
            'area' => SysArea::cityTree(),
        ]);
        return parent::render();
    }
}
