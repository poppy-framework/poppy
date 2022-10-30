<?php

namespace Demo\Forms;

class FormSelect extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Select';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->select('select', 'Select')
            ->options([
                'a' => 'apple',
                'b' => 'pear',
                'orange',
            ])->help('选择一项');
        // 添加 code 代码
        $code = <<<CODE
\$this->select('select', 'Select')
    ->options([
        'a' => 'apple',
        'b' => 'pear',
        'orange',
    ])->help('选择一项');
CODE;
        $this->code('select-code', 'Code@Select')->default($code);
    }
}