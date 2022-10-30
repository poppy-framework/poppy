<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormIcon extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = '图标选择(暂时不处理)';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->icon('icon', 'Icon')->rules([
            Rule::required(),
        ])->default('xx')->autofocus()->help('图标选择库必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->icon('icon', 'Icon')->rules([
    Rule::required(),
])->default('xx')->autofocus()->help('图标选择库必填');
CODE;
        $this->code('icon-code', 'Code@Icon')->default($code);
    }
}