<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Tools;

use Html;
use Illuminate\Support\Str;

/**
 * 创建按钮
 */
class BaseButton
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

        // 默认加入tooltip
        if (!Str::contains($class, 'J_tooltip')) {
            $class .= ' J_tooltip ';
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
        return ' ' . Html::link($this->url, $this->title, $this->attribute, null, false) . ' ';
    }
}
