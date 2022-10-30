<?php

namespace Demo\Forms;

class FormHidden extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Hidden';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->hidden('info', 'Info')->default('user')->help('隐藏域');
        // 添加 code 代码
        $code = <<<CODE
\$this->hidden('info', 'Info')->default('user')->help('隐藏域');
CODE;
        $this->code('info-code', 'Code@Info')->default($code);
    }
}