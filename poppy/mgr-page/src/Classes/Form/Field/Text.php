<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Poppy\MgrPage\Classes\Form\Field;
use Poppy\MgrPage\Classes\Form\Traits\PlainInput;

class Text extends Field
{
    use PlainInput;

    /**
     * @var string 默认类型(Number 可覆盖)
     */
    protected string $type = 'text';

    /**
     * Render this filed.
     *
     * @return Factory|View
     */
    public function render()
    {
        $this->initPlainInput();

        $this->defaultAttribute('id', $this->id)
            ->defaultAttribute('class', 'layui-input ' . $this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder());

        $this->addVariables([
            'type' => $this->type,
        ]);

        return parent::render();
    }
}
