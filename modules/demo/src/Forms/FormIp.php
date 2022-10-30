<?php

namespace Demo\Forms;

class FormIp extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Ip输入';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->ip('ip', 'Ip')->rules(['required', 'ip'])->help('IP输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->ip('ip', 'Ip')->rules(['required', 'ip'])->help('IP输入框');
CODE;
        $this->code('ip-code', 'Code@Ip')->default($code);

    }
}