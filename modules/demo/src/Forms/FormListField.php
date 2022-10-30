<?php

namespace Demo\Forms;

class FormListField extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'ListField';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('list-field  无法验证');
    }
}