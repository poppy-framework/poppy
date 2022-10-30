<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormUrl extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'URL';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->url('url', 'Url')->rules([
            Rule::required(),
        ])->help('URL输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->url('url', 'Url')->rules([
    Rule::required(),
])->help('URL输入框');
CODE;
        $this->code('url-code', 'Code@Url')->default($code);
        $this->divider();

        $this->url('nullable', 'Nullable')->rules([
            Rule::nullable(),
        ])->help('URL输入框可为空');
        // 添加 code 代码
        $code = <<<CODE
\$this->url('nullable', 'Nullable')->rules([
    Rule::nullable(),
])->help('URL输入框可为空');
CODE;
        $this->code('nullable-code', 'Code@Nullable')->default($code);
    }
}