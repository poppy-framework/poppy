<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;

class FormMultiImage extends FormBaseWidget
{

    protected $title = 'MultiImage';


    public function data(): array
    {
        return [
            'image' => [
                py_faker()->imageUrl(),
                py_faker()->imageUrl(480, 640),
                py_faker()->imageUrl(480),
            ],
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $token = app('tymon.jwt.auth')->fromUser(PamAccount::first());
        $this->multiImage('image', '图片, 默认, 可上传')->rules([
            Rule::required(),
        ])->token($token)->auto(true);
        // 添加 code 代码
        $code = <<<CODE
\$token = app('tymon.jwt.auth')->fromUser(PamAccount::first());
\$this->image('image', '图片, 默认, 可上传')->rules([
    Rule::required(),
])->token($token);
CODE;

        $this->multiImage('multi', '图片, 默认, 可上传')->rules([
            Rule::required(),
        ])->number(50)->token($token)->auto(true);
        $this->code('image-code', 'Code@图片, 默认, 可上传')->default($code);
        $this->divider();

    }
}