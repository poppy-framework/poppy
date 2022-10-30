<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormTime extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Time';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->time('time', 'Time')->rules([
            Rule::required(),
        ])->help('时间必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->time('time', 'Time')->rules([
    Rule::required(),
])->help('时间必填');
CODE;
        $this->code('time-code', 'Code@Time')->default($code);
    }
}