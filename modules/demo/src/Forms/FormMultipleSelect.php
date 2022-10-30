<?php

namespace Demo\Forms;

class FormMultipleSelect extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'MultipleSelect';


    public function data(): array
    {
        return [
            'data' => [1],
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('select 多选');

        $this->multipleSelect('data', 'Fill')
            ->options([
                1 => 'Name',
                2 => 'Name2',
                3 => 'Name3',
            ])->attribute([
                'paging' => true,
                'size'   => 2,
                'filter' => true,
            ]);
    }
}