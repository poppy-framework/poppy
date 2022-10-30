<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormMobile extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Mobile';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->mobile('phone', 'Phone')->rules([
            Rule::nullable(),
        ])->help('手机号输入框可不填');
        // 添加 code 代码
        $code = <<<CODE
\$this->mobile('phone', 'Phone')->rules([
    Rule::nullable(),
])->help('手机号输入框可不填');
CODE;
        $this->code('phone-code', 'Code@Phone')->default($code);
        $this->divider();

        $this->mobile('mobile', 'Mobile')->rules([
            Rule::required(),
        ])->help('手机号输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->mobile('mobile', 'Mobile')->rules([
    Rule::required(),
])->help('手机号输入框必填');
CODE;
        $this->code('mobile-code', 'Code@Mobile')->default($code);

    }
}
