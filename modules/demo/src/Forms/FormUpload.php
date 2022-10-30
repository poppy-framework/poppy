<?php

namespace Demo\Forms;

class FormUpload extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = '文件上传';


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->file('image')->image();
        $this->file('file')->file();
        $this->file('video')->video();
        $this->file('audio')->audio();
    }
}