<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormMonth extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Month';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->month('month', 'Month')->default('2020-02')->help('月份输入框非必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->month('month', 'Month')->default('2020-02')->help('月份输入框非必填');
CODE;
        $this->code('month-code', 'Code@Month')->default($code);
        $this->divider();

        $this->month('month_require', 'Require')->rules([
            Rule::required(),
        ])->help('月份输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->month('month_require', 'Require')->rules([
    Rule::required(),
])->help('月份输入框必填');
CODE;
        $this->code('month_require-code', 'Code@Require')->default($code);
    }
}