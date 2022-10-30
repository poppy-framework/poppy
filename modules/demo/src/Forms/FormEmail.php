<?php

namespace Demo\Forms;

class FormEmail extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '邮件配置';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->email('email', 'Email')->help('邮箱输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->email('email', 'Email')->help('邮箱输入框');
CODE;
        $this->code('email-code', 'Code@Email')->default($code);
    }
}
