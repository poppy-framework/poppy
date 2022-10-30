<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormDateRange extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = '日期输入框';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->dateRange('daterange', 'Date Range')->placeholder('请选择日期范围')->help('日期范围选择');
        // 添加 code 代码
        $code = <<<CODE
\$this->dateRange('daterange', 'Date Range')->placeholder('请选择日期范围')->help('日期范围选择');
CODE;
        $this->code('daterange-code', 'Code@Date Range')->default($code);
        $this->divider();

        $this->dateRange('disable', 'Disable')->disable()->help('禁止输入');
        // 添加 code 代码
        $code = <<<CODE
\$this->dateRange('disable', 'Disable')->disable()->help('禁止输入');
CODE;
        $this->code('disable-code', 'Code@Disable')->default($code);
        $this->divider();

        $this->dateRange('daterange_required', 'Date Range Required')->rules([
            Rule::required(),
        ])->help('输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->dateRange('daterange_required', 'Date Range Required')->rules([
    Rule::required(),
])->help('输入框必填');
CODE;
        $this->code('daterange_required-code', 'Code@Date Range Required')->default($code);
    }
}