<?php

namespace Demo\Forms;

class FormFieldSet extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '表单项组合';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->fieldset('用户信息', static function () {
            return 'user_info';
        })->end();
        // 添加 code 代码
        $code = <<<CODE
\$this->fieldset('用户信息', static function () {
    return 'user_info';
})->end();
CODE;
        $this->code('user-code', 'Code@用户信息')->default($code);
        $this->divider();

        $this->fieldset('info', static function () {
            return 'zh';
        })->start('test');
        // 添加 code 代码
        $code = <<<CODE
\$this->fieldset('info', static function () {
    return 'zh';
})->start('test');
CODE;
        $this->code('info-code', 'Code@info')->default($code);
    }
}