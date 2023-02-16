<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use JsonException;
use Poppy\MgrPage\Classes\Form\Field;

class Textarea extends Field
{
    /**
     * Default rows of textarea.
     *
     * @var int
     */
    protected int $rows = 5;

    /**
     * Set rows of textarea.
     *
     * @param int $rows
     *
     * @return $this
     */
    public function rows(int $rows = 5): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function render()
    {
        if (is_array($this->value)) {
            $this->value = json_encode($this->value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        }

        return parent::render()->with(['rows' => $this->rows]);
    }
}
