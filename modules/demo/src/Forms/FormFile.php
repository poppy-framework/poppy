<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;

class FormFile extends FormBaseWidget
{
    /**
     * 表单标题
     *
     * @var string
     */
    protected $title = 'File';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->file('file', 'File')->rules([
            Rule::file(),
        ])->help('文件上传');
        // 添加 code 代码
        $code = <<<CODE
\$this->file('file', 'File')->rules([
    Rule::file(),
])->help('文件上传');
CODE;
        $this->code('file-code', 'Code@File')->default($code);
        $this->file('video', '视频')->rules([
            Rule::file(),
        ])->help('上传视频')->video()->pam(PamAccount::first());
        // 添加 code 代码
        $code = <<<CODE
\$this->file('video', '视频')->rules([
    Rule::file(),
])->help('上传视频');
CODE;
        $this->code('file-code', 'Code@File')->default($code);
    }
}
