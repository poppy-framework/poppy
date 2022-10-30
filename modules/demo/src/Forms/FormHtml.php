<?php

namespace Demo\Forms;

class FormHtml extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'HTML';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->html('<span style="color: red">1</span>')->plain()->help('HTML显示');
        // 添加 code 代码
        $code = <<<CODE
\$this->html('<span style="color: red">1</span>')->plain()->help('HTML显示');
CODE;
        $this->code('html-code', 'Code@Html')->default($code);
    }
}