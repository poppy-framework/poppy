<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormCurrency extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = '货币输入';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->currency('amount', 'Amount')->rules([
            Rule::required(),
        ])->help('输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->currency('amount', 'Amount')->rules([
    Rule::required(),
])->help('输入框必填');
CODE;
        $this->code('amount-code', 'Code@Amount')->default($code);
        $this->divider();

        $this->currency('money', 'Money')->help('货币输入框')->prepare(2);
        // 添加 code 代码
        $code = <<<CODE
\$this->currency('money', 'Money')->help('货币输入框')->prepare(2);
CODE;
        $this->code('money-code', 'Code@Money')->default($code);
    }
}