<?php

declare(strict_types=1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Presenter;

class MultipleSelect extends Select
{
    protected int $size = 8;

    public function size($size = 8): self
    {
        $this->size = $size;
        return $this;
    }

    public function variables(): array
    {
        return array_merge(parent::variables(), [
            'size' => $this->size,
        ]);
    }
}
