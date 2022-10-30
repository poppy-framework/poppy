<?php

namespace Demo\Forms;

class FormButton extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'button框';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->button('kaka');
        // 添加 code 代码
        $code = <<<CODE
\$this->button('kaka');
CODE;
        $this->code('kaka-code', 'Code@kaka')->default($code);
        $this->divider();

        $this->button('kaka')->info();
        // 添加 code 代码
        $code = <<<CODE
\$this->button('kaka')->info();
CODE;
        $this->code('kaka-code', 'Code@kaka')->default($code);
        $this->divider();

        $this->button('kaka')->small();
        // 添加 code 代码
        $code = <<<CODE
\$this->button('kaka')->small();
CODE;
        $this->code('kaka-code', 'Code@kaka')->default($code);
    }
}