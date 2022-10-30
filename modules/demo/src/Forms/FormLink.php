<?php

namespace Demo\Forms;

class FormLink extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '各种可以激活的链接地址';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->link('kaka')->url('https://www.baidu.com/')->help('链接地址');
        // 添加 code 代码
        $code = <<<CODE
\$this->link('kaka')->url('https://www.baidu.com/')->help('链接地址');
CODE;
        $this->code('kaka-code', 'Code@Kaka')->default($code);

    }
}