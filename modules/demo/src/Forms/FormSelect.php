<?php

declare(strict_types = 1);

namespace Demo\Forms;

class FormSelect extends FormBaseWidget
{
    /**
     * 表单标题
     *
     * @var string
     */
    protected $title = 'Select';

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->select('select', 'Select')
            ->options([
                'a' => 'apple',
                'b' => 'pear',
                'orange',
            ]);

        $this->code('select-code', '选择代码')->default(<<<CODE
\$this->select('select', 'Select')
    ->options([
        'a' => 'apple',
        'b' => 'pear',
        'c' => 'orange',
    ]);
CODE);
        $this->select('select-search', 'Select')
            ->options([
                'a' => 'apple',
                'b' => 'pear',
                'orange',
            ])->help('支持搜索')->searchable();
        $this->code('select-search-code', '支持搜索')->default(<<<CODE
\$this->select('select', 'Select')
    ->options([
        'a' => 'apple',
        'b' => 'pear',
        'c' => 'orange',
    ])->searchable();
CODE);
    }
}
