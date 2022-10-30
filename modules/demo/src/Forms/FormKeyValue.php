<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormKeyValue extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'keyValue';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->keyValue('xx', 'Xx')->rules([
            Rule::required(),
        ])->help('KeyValue输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->keyValue('xx', 'Xx')->rules([
    Rule::required(),
])->help('KeyValue输入框');
CODE;
        $this->code('ip-code', 'Code@Ip')->default($code);

    }
}