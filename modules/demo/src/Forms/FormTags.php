<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamRole;

class FormTags extends FormBaseWidget
{


    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Tags (标签)';


    public function data(): array
    {
        return [
            'tags' => [2],
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->tags('tags', 'Tags')
            ->options(PamRole::getLinear('backend', 'id'))->rules([
                Rule::required(),
            ])->help('标签必选且最多选择4项');
        // 添加 code 代码
        $code = <<<CODE
\$this->tags('tags', 'Tags')
    ->options(PamRole::getLinear('backend', 'id'))
    ->rules([
        Rule::required(),
    ])->help('标签必选且最多选择4项');
CODE;
        $this->code('tags-code', 'Code@Tags')->default($code);
    }
}