<?php

namespace Demo\Forms;

use Carbon\Carbon;
use Poppy\Framework\Validation\Rule;

class FormDate extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '日期输入框';

    public function data():array
    {
        return [
            'date' => Carbon::now()->toDateString(),
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {

        $this->date('date', '日期输入')->placeholder('输入正确日期格式')->rules([
            Rule::dateFormat('Y-m-d'),
        ])->help('日期输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->date('date', '日期输入')->placeholder('输入正确日期格式')->rules([
    Rule::dateFormat('Y-m-d'),
])->help('日期输入框');
CODE;
        $this->code('date-code', 'Code@日期输入')->default($code);
        $this->divider();

        $this->date('disable')->disable()->help('禁止输入');
        // 添加 code 代码
        $code = <<<CODE
\$this->date('disable')->disable()->help('禁止输入');
CODE;
        $this->code('disable-code', 'Code@disable')->default($code);
        $this->divider();

        $this->date('date_format')->placeholder('日期输入')->rules([
            Rule::required(),
            Rule::date(),
        ])->help('输入日期正确格式');
        // 添加 code 代码
        $code = <<<CODE
\$this->date('date_format')->placeholder('日期输入')->rules([
    Rule::required(),
    Rule::date(),
])->help('输入日期正确格式');
CODE;
        $this->code('date_format-code', 'Code@date_format')->default($code);
        $this->divider();

        $this->date('default', '默认值')->default('2020-11-20')->help('设置默认值');
        // 添加 code 代码
        $code = <<<CODE
\$this->date('default','默认值')->default('2020-11-20')->help('设置默认值');
CODE;
        $this->code('default-code', 'Code@default')->default($code);
        $this->divider();

        $this->date('icon', '无icon')->prepend('')->help('无图标显示');
        // 添加 code 代码
        $code = <<<CODE
\$this->date('icon', '无icon')->prepend('')->help('无图标显示');
CODE;
        $this->code('icon-code', 'Code@icon')->default($code);
        $this->divider();
    }
}