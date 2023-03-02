<?php

namespace Demo\Forms;

class FormTips extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Tip';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('tip', '提示')
            ->help('此提示公开显示');
        $this->text('tip_hidden', '提示(图标)')
            ->help('此提示不公开显示', '', false);
        $this->text('tip_icon', '提示(其他图标)')
            ->help('此提示不公开显示', 'layui-icon-util', false);
    }
}