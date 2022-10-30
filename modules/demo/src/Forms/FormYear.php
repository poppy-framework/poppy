<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormYear extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Year';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->year('year', 'Year')->rules([
            Rule::required(),
        ])->help('年份必选');
        // 添加 code 代码
        $code = <<<CODE
\$this->year('year', 'Year')->rules([
    Rule::required(),
])->help('年份必选');
CODE;
        $this->code('year-code', 'Code@Year')->default($code);
    }
}