<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Traits;

trait PlainInput
{

    protected function initPlainInput(): void
    {
        if (empty($this->view)) {
            $this->view = 'py-mgr-page::tpl.form.input';
        }
    }

    protected function defaultAttribute($attribute, $value): self
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            $this->attribute($attribute, $value);
        }

        return $this;
    }
}
