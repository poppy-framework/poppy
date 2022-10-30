<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormDecimal extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '数字输入';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->decimal('decimal', 'Decimal')->rules([
            Rule::required(),
        ])->default('1')->help('数字输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->decimal('decimal', 'Decimal')->rules([
    Rule::required(),
])->default('1')->help('数字输入框必填');
CODE;
        $this->code('decimal-code', 'Code@Decimal')->default($code);
    }
}