<?php

namespace Demo\Forms;

class FormDivider extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Divider';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('分割线')->help('分割线显示');
        // 添加 code 代码
        $code = <<<CODE
\$this->divider('分割线')->help('分割线显示');
CODE;
        $this->code('分割线-code', 'Code@分割线')->default($code);
    }
}