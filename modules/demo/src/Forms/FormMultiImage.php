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
            'image'        => [
                py_faker()->imageUrl(),
                py_faker()->imageUrl(480, 640),
                py_faker()->imageUrl(480),
            ],
            'image-resize' => [
                'https://test-oss.iliexiang.com/_res/images/01.jpg',
                'https://test-oss.iliexiang.com/_res/images/02.jpg',
                'https://test-oss.iliexiang.com/_res/images/03.jpg',
                'https://test-oss.iliexiang.com/_res/images/04.jpg',
                'http://oss-qiniu.sour-lemon.com/_res/image/01.jpg',
                'http://examples-1251000004.cos.ap-shanghai.myqcloud.com/sample.jpeg',
                'https://e-share.obs.cn-north-1.myhuaweicloud.com/example.jpg',
                'http://oss-qiniu.sour-lemon.com/_res/video/maria-20.7m.mp4',
                'https://oss-console-img-demo-cn-hangzhou.oss-cn-hangzhou.aliyuncs.com/video.mp4',
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

        $this->multiImage('image-resize', '图片, 缩略图')->rules([
            Rule::required(),
        ])->number(50)->token($token)->auto(true)
            ->help('列表加载缩略图, 并非加载原图, 加速图片访问请求');
        $this->code('image-code', 'Code@图片, 默认, 可上传')->default($code);
        $this->divider();

    }
}