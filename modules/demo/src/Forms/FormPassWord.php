<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormPassWord extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'PassWord';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->password('password', 'Password')->rules([
            Rule::confirmed(),
            Rule::required(),
            'regex:/^[A-Za-z0-9]{4,}$/',
        ])->help('密码输入框必须填写');
        // 添加 code 代码
        $code = <<<CODE
\$this->password('password', 'Password')->rules([
    Rule::required(),
    'regex:/^[A-Za-z0-9]{4,}$/',
])->help('密码输入框必须填写');
CODE;
        $this->code('password-code', 'Code@Password')->default($code);
        $this->divider();

        $this->password('password_confirmation', 'Password_Confirmation')->rules([
            'regex:/^[A-Za-z0-9]{4,}$/',
        ])->placeholder('由 A-Za-z0-9 至少4位组成')->help('确认密码与密码必须一致');
        // 添加 code 代码
        $code = <<<CODE
\$this->password('password_confirmation', 'Password_Confirmation')->rules([
    'regex:/^[A-Za-z0-9]{4,}$/',
])->placeholder('由 A-Za-z0-9 至少4位组成')->help('确认密码与密码必须一致');
CODE;
        $this->code('password_confirmation-code', 'Code@Password_Confirmation')->default($code);

    }
}
