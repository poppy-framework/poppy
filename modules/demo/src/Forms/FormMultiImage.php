<?php

namespace Demo\Forms;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;

class FormMultiImage extends FormBaseWidget
{

    protected $title = 'MultiImage';


    /**
     * @throws ApplicationException
     */
    public function data(): array
    {
        return [
            'images'       => [
                py_faker()->imageUrl(),
                py_faker()->imageUrl(480, 640),
                py_faker()->imageUrl(480),
            ],
            'image-resize' => [
                'https://test-oss.iliexiang.com/_res/images/01.jpg',
                'https://test-oss.iliexiang.com/_res/images/02.gif',
                'https://test-oss.iliexiang.com/_res/images/03.jpg',
                'https://test-oss.iliexiang.com/_res/images/04.jpg',
                'http://oss-qiniu.sour-lemon.com/_res/image/01.jpg',
                'http://examples-1251000004.cos.ap-shanghai.myqcloud.com/sample.jpeg',
                'https://e-share.obs.cn-north-1.myhuaweicloud.com/example.jpg',
                'http://oss-qiniu.sour-lemon.com/_res/video/maria-20.7m.mp4',
                'https://oss-console-img-demo-cn-hangzhou.oss-cn-hangzhou.aliyuncs.com/video.mp4',
            ],
            'images-error' => [
                'blob://' . config('app.url') . '/00000-0000-0000',
            ],
        ];
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $token = app('tymon.jwt.auth')->fromUser(PamAccount::first());

        $this->multiImage('images-3', '3张图限制')->rules([
            Rule::required(),
            Rule::urls(),
        ])->number(3)->token($token)->auto(true)->help('多个图片');
        $this->divider();
        $this->multiImage('images-3-auto', '3张手动上传')->rules([
            Rule::required(),
            Rule::urls(),
        ])->number(3)->token($token)->help('多个图片');
        $this->divider();
        $this->multiImage('images', '图片, 默认, 可上传')->rules([
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

        $this->multiImage('images-error', '错误的 Blob 图')->rules([
            Rule::required(),
            Rule::urls(),
        ])->number(50)->token($token)->auto(true)->help('多个图片');
        $this->divider();


    }
}