<?php

declare(strict_types = 1);

namespace Demo\Forms;

class FormSelectDo extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'SelectDo';


    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->selectDo('select', 'Select')
            ->options([
                'a' => 'apple',
                'b' => 'pear',
                'c' => 'orange',
            ])->help('选择一项内容, 基于当前的 name 来进行跳转, 一般用于快速导航, location 支持两个参数, 可以设定跳转地址和指定参数')->location();
        // 添加 code 代码
        $code = <<<CODE
\$this->selectDo('select', 'Select')
    ->options([
        'a' => 'apple',
        'b' => 'pear',
        'orange',
    ])->help('选择一项')->location();
CODE;
        $this->code('select-code', 'Code@Select')->default($code);
    }
}