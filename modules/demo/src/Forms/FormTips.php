<?php

declare(strict_types = 1);

namespace Demo\Forms;

class FormTips extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Tip';


    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->text('tip', '提示')
            ->help('此提示公开显示');

        // 默认图标
        $this->divider('提示 - 使用默认图标');
        $this->text('tip-icon', '提示(使用默认图标)')
            ->help('此图标使用默认图标', false);
        $code = <<<CODE
\$this->text('tip-icon', '提示(使用默认图标)')
    ->help('此图标使用默认图标', true);
CODE;
        $this->code('tip-icon-code', 'code@提示(默认图标)')->default($code);

        // bi 自定义图标
        $this->divider('提示 - 修改为其他图标');
        $this->text('tip-icon-bi', '提示(Bi 图标)')
            ->help('此图标使用的是 bootstrap icon 图标', false, 'bi-bootstrap');
        $code = <<<CODE
\$this->text('tip-icon-bi', '提示(Bi 图标)')
    ->help('此图标使用的是 bootstrap icon 图标', 'bi-bootstrap', false);
CODE;
        $this->code('tip-icon-bi-code', 'code@提示(Bi 图标)')->default($code);
    }
}