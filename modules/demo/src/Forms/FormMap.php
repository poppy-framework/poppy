<?php

namespace Demo\Forms;

class FormMap extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Map';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('map  无法验证');

    }
}