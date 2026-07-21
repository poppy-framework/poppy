<?php

namespace Demo\Forms;

class FormMultipleSelect extends FormBaseWidget
{
    /**
     * 表单标题
     *
     * @var string
     */
    protected $title = 'MultipleSelect';

    public function data(): array
    {
        return [
            'integer'      => 1,
            'array'        => [1],
            'array-string' => ['1'],
            'string'       => '1',
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('select 多选');

        $options = [
            1 => 'Name',
            2 => 'Name2',
            3 => 'Name3',
        ];

        $conf = [
            'paging' => true,
            'size'   => 2,
            'filter' => true,
        ];

        $this->multipleSelect('integer', '数值')
            ->options($options)->attribute($conf);
        $this->multipleSelect('array', '数值')
            ->options($options)->attribute($conf);
        $this->multipleSelect('array-string', '字符数组')
            ->options($options)->attribute($conf);
        $this->multipleSelect('string', '字符')
            ->options($options)->attribute($conf);
    }
}
