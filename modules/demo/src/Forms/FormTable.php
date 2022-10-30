<?php

namespace Demo\Forms;

class FormTable extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Table';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('table 需测试');
    }
}