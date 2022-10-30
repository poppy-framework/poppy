<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormTimeRange extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '时间范围选择';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->timeRange('time_range', '2021-1-19')->rules([
            Rule::required(),
        ])->help('时间范围必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->timeRange('time_range', '2021-1-19')->rules([
    Rule::required(),
])->help('时间范围必填');
CODE;
        $this->code('time_range-code', 'Code@2021-1-19')->default($code);
    }
}