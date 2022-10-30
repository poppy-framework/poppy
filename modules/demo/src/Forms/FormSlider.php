<?php

namespace Demo\Forms;

class FormSlider extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Slider(滑动选择)';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->slider('slider', '滑动')->options([
            'max'     => 100,
            'min'     => 1,
            'step'    => 1,
            'postfix' => 'years old',
        ]);
    }
}