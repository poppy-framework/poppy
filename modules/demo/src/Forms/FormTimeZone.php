<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormTimeZone extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '时区选择';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->timezone('timezone', 'Timezone')->rules([
            Rule::required(),
        ])->help('时区选择项');
        // 添加 code 代码
        $code = <<<CODE
\$this->timezone('timezone', 'Timezone')->rules([
    Rule::required(),
])->help('时区选择项');
CODE;
        $this->code('timezone-code', 'Code@Timezone')->default($code);
    }
}