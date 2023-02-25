<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;

/**
 * 请求操作
 * @deprecated 4.2
 * @removed    5.0
 */
class HtmlOperation implements Renderable
{

    /**
     * 标题
     * @var string
     */
    protected string $title;


    /**
     * 地址
     * @var string
     */
    protected string $url;

    /**
     * @var array|mixed
     */
    private $attribute;


    public function __construct($btn_text, $url, $attribute = [])
    {
        $this->title     = $btn_text;
        $this->url       = $url;
        $this->attribute = $attribute;

        $class = $this->attribute['class'] ?? '';

        // 只有图标会加入默认的 tooltip
        if (!Str::contains($class, 'J_tooltip') && Str::contains($btn_text, '<i')) {
            $class .= ' J_tooltip ';
            if (!isset($attribute['title'])) {
                $this->attribute['title'] = $btn_text;
            }
        }
        $this->attribute['class'] = $class;
    }

    /**
     * Render CreateButton.
     *
     * @return string
     */
    public function render(): string
    {
        return ' ' . app('html')->link($this->url, $this->title, $this->attribute, null, false) . ' ';
    }
}
