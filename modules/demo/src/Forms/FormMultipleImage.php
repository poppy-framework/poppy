<?php

namespace Demo\Forms;

class FormMultipleImage extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'MultipleImage';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('multipleImage 无法验证');
    }
}