<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormNumber extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Number';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->number('number', 'Number')->rules([
            Rule::nullable(),
        ])->help('数字输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->number('number', 'Number')->rules([
    Rule::nullable(),
])->help('数字输入框');
CODE;
        $this->code('number-code', 'Code@Number')->default($code);
        $this->divider();

        $this->number('number_min_district', '数字(非必选[最小值2])')
            ->rules([
                'min:2',
                Rule::nullable(),
            ])->help('数字输入框最小值为2');
        // 添加 code 代码
        $code = <<<CODE
\$this->number('number_min_district', '数字(非必选[最小值2])')
    ->rules([
        'min:2',
        Rule::nullable(),
    ])->help('数字输入框最小值为2');
CODE;
        $this->code('number_min_district-code', 'Code@数字(非必选[最小值2])')->default($code);
        $this->divider();

        $this->number('number_max_district', '数字(必选[3-100])')
            ->rules([
                'min:3',
                'max:100',
                Rule::required(),
            ])->help('输入值条件 最小是3最大是100');
        // 添加 code 代码
        $code = <<<CODE
\$this->number('number_max_district', '数字(必选[3-100])')
    ->rules([
        'min:3',
        'max:100',
        Rule::required(),
    ])->help('输入值条件 最小是3最大是100');
CODE;
        $this->code('number_max_district-code', 'Code@数字(必选[3-100])')->default($code);
    }
}
