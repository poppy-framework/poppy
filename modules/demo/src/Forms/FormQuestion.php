<?php

namespace Demo\Forms;

class FormQuestion extends FormBaseWidget
{
    /**
     * 表单标题
     *
     * @var string
     */
    protected $title = 'Question(问题)';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->question('keyword', 'keywords')
            ->default(['羊毛衫', '巧笑'])
            ->help('关键词支持默认数组,可以通过拖拽来调整顺序');
        $this->question('keyword-a', 'keywords')
            ->default('羊毛衫')
            ->help('关键词支持字符串,可以通过拖拽来调整顺序');
        $code = <<<CODE
\$this->keyword('keyword', 'keywords')
    ->default(['羊毛衫', '巧笑'])
    ->help('关键词支持默认数组,可以通过拖拽来调整顺序');
\$this->keyword('keyword-a', 'keywords')
    ->default('羊毛衫')
    ->help('关键词支持字符串,可以通过拖拽来调整顺序');
CODE;
        $this->code('code', 'Code')->default($code);
    }
}
