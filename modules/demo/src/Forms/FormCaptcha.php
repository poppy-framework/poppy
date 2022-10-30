<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

/**
 * 验证码
 */
class FormCaptcha extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '验证码框';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->captcha('test', 'xxx')->rules([
            Rule::required(),
        ])->help('内容必填');
        // 添加 code 代码
        $code = <<<CODE
\$this->captcha('test', 'xxx')->rules([
    Rule::required(),
])->help('内容必填');
CODE;
        $this->code('test-code', 'Code@xxx')->default($code);
        $this->divider();

        $this->captcha('test_v_2', 'xxxx')->rules([
            'captcha',
        ])->help('输入内容需正确');
        // 添加 code 代码
        $code = <<<CODE
\$this->captcha('test_v_2', 'xxxx')->rules([
    'captcha',
])->help('输入内容需正确');
CODE;
        $this->code('test_v_2-code', 'Code@xxxx')->default($code);
    }
}
