<?php

namespace Demo\Forms;


class FormArea extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Area';


    /**
     * Build a form here.
     */
    public function form()
    {
        call_user_func_array([$this, 'area'], ['Area']);

        $this->area('area', 'Area');
        $this->divider();
    }
}