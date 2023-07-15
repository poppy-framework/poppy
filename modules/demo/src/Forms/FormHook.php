<?php

declare(strict_types = 1);

namespace Demo\Forms;

class FormHook extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Hook';

    public function data(): array
    {
        return [
            'place_id' => 1,
        ];
    }

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->hook('place_id', '选择占位')->service('poppy.ad.form_place_select');
        $this->hook('category_id', '选择类别')->service('poppy.category.form_category_select', [
            'type' => 'default'
        ]);
    }
}