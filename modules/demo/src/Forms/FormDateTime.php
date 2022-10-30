<?php

namespace Demo\Forms;

class FormDateTime extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '日期时间输入框';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->datetime('datetime', 'Datetime')->help('日期时间输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->datetime('datetime')->help('日期时间输入框');
CODE;
        $this->code('datetime-code', 'Code@Datetime')->default($code);
        $this->divider();

        $this->datetime('disable', 'Disable')->disable()->placeholder('禁用')->help('输入框禁用');
        // 添加 code 代码
        $code = <<<CODE
\$this->datetime('disable', 'Disable')->disable()->placeholder('禁用')->help('输入框禁用');
CODE;
        $this->code('disable-code', 'Code@Disable')->default($code);
        $this->divider();

        $this->datetime('icon', '无icon')->prepend('')->help('无图标输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->datetime('icon', '无icon')->prepend('')->help('无图标输入框');
CODE;
        $this->code('icon-code', 'Code@无icon')->default($code);
        $this->divider();

    }
}