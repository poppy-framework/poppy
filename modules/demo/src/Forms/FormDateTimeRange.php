<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormDateTimeRange extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '日期时间范围输入框';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->datetimeRange('datetime_range', 'Date Range')->help('日期范围输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->datetime('datetime')->help('日期时间输入框');
CODE;
        $this->code('datetime_range-code', 'Code@Date Range')->default($code);
        $this->divider();

        $this->datetimeRange('datetime_range_required', 'Date Range Required')->rules([
            Rule::required(),
        ])->help('日期范围输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->datetimeRange('datetime_range_required', 'Date Range Required')->rules([
    Rule::required(),
])->help('日期范围输入框必填');
CODE;
        $this->code('datetime_range_required-code', 'Code@Date Range Required')->default($code);
    }
}