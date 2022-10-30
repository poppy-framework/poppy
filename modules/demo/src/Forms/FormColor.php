<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormColor extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '颜色(仅仅支持一种格式)';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->color('test_v_1', 'Test1')->default('#ff0022')->rules([
            Rule::required(),
        ])->help('颜色选择库');
        // 添加 code 代码
        $code = <<<CODE
\$this->color('test_v_1', 'Test1')->default('#ff0022')->rules([
    Rule::required(),
])->help('颜色选择库');
CODE;
        $this->code('test_v_1-code', 'Code@Test1')->default($code);
    }
}