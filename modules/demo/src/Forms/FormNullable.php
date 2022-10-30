<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormNullable extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'NullAble';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('nullable', 'NullAble')->rules([
            Rule::nullable(),
        ])->placeholder('输入文本内容')->help('文本输入框非必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('nullable', 'NullAble')->rules([
    Rule::nullable(),
])->placeholder('输入文本内容')->help('文本输入框非必填');
CODE;
        $this->code('nullable-code', 'Code@NullAble')->default($code);
    }
}