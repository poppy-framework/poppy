<?php

namespace Demo\Forms;


use Poppy\MgrPage\Classes\Form\Field\Number;

class FormTableInput extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Table';


    /**
     * Build a form here.
     */
    public function form(): void
    {
        $types = [
            'wz'  => '王者荣耀',
            'lol' => '英雄联盟',
        ];
        foreach ($types as $key => $desc) {
            $table[] = [
                (new Number('send_rate_' . $key, [$desc]))->default((int) sys_setting('send_rate_' . $key)),
            ];
        }
        $this->tableInput('x', '批量编辑')->table($table);
    }
}