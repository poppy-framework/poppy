<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormRate extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Rate(比例)';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->rate('rate', 'Rate')->rules([
            Rule::required(),
            'regex:/^(\d{0,2}(\.\d{1,2})?|100(\.00?)?)$/',
        ])->help('比例输入框必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->rate('rate', 'Rate')->rules([
    Rule::required(),
    'regex:/^(\d{0,2}(\.\d{1,2})?|100(\.00?)?)$/',
])->help('比例输入框必填');
CODE;
        $this->code('rate-code', 'Code@Rate')->default($code);
        $this->divider();

        $this->rate('test', '比例')->help('比例输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->rate('test', '比例')->help('比例输入框');
CODE;
        $this->code('test-code', 'Code@比例')->default($code);
    }
}