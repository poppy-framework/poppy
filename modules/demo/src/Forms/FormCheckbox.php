<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormCheckbox extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Checkbox (多选框)';

    public function data(): array
    {
        return [
            'checkbox_required' => [
                'a',
            ],
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->checkbox('check_range', 'Check(Range)')->options(range(1, 10))->rules([
            Rule::required(),
        ])->help('一系列数据, 没有默认选择');
        $this->checkbox('checkbox_required', 'Checkbox')->options([
            'a' => 'Name',
            'b' => 'Name2',
            'c' => 'Name3',
        ])->rules([
            Rule::required(),
        ])->help('必选 至少一项');
        // 添加 code 代码
        $code = <<<CODE
\$this->checkbox('checkbox_required', 'Checkbox')->options([
    'a' => 'Name',
    'b' => 'Name2',
    'c' => 'Name3',
])->rules([
    Rule::required()
])->help('必选 至少一项');
CODE;
        $this->code('checkbox_required-code', 'Code@Checkbox')->default($code);
        $this->divider();

        $this->checkbox('checkbox_all', '全选')->options([
            '1' => 'play',
            '2' => 'eat',
            '3' => 'drink',
        ])->stacked()->canCheckAll()->help('支持全选且竖排显示');
        // 添加 code 代码
        $code = <<<CODE
\$this->checkbox('checkbox_all', '全选')->options([
    '1' => 'play',
    '2' => 'eat',
    '3' => 'drink',
])->stacked()->canCheckAll()->help('支持全选且竖排显示');
CODE;
        $this->code('checkbox_all-code', 'Code@全选')->default($code);
        $this->divider();

        $this->checkbox('checkbox_func', 'func')->default([1])
            ->options([1 => 'Func', 2 => 'Func2', 3 => 'Func3'])
            ->help('默认选择项');
        // 添加 code 代码
        $code = <<<CODE
\$this->checkbox('checkbox_func', 'func')->checked(['1'])
    ->options([1 => 'Func', 2 => 'Func2', 3 => 'Func3'])
    ->help('默认选择项');
CODE;
        $this->code('checkbox_func-code', 'Code@func')->default($code);
    }
}