<?php

namespace Demo\Forms;

class FormSwitchField extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'SwitchField';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('开关');
        $this->switch('is_open', '开关');
        // 添加 code 代码
        $code = <<<CODE
\$this->switch('is_open', '开关');
CODE;
        $this->code('is_open-code', 'Code@开关')->default($code);
    }
}