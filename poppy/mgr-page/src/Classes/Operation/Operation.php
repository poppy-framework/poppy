<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

use Form;
use Html;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;

/**
 * @method self primary()       主要
 * @method self normal()        默认
 * @method self warm()          暖色
 * @method self danger()        危险
 * @method self disabled()      禁用
 * @method self default()       默认
 *
 * @method self lg()            大型
 * @method self sm()            小型
 * @method self xs()            迷你
 *
 * @method self round()         圆角
 * @method self only()          仅仅显示图标
 */
abstract class Operation implements Renderable
{
    /**
     * 禁用
     * @var bool
     */
    protected bool $disabled = false;


    /**
     * 属性
     * @var array
     */
    protected array $attributes = [];

    /**
     * 样式
     * @var array
     */
    protected array $classes = [];

    /**
     * 朴素模式
     * @var string
     */
    protected string $plain = '';

    /**
     * 圆角
     * @var bool
     */
    protected bool $round = false;

    /**
     * 按钮的类型
     * @var string
     */
    protected string $type = '';

    /**
     * 大小
     * @var string
     */
    protected string $size = 'xs';

    /**
     * icon
     * @var string
     */
    protected string $icon = '';

    /**
     * 是否只显示 ICON
     * @var bool
     */
    protected bool $only = false;

    /**
     * 文字模式
     * @var bool
     */
    protected bool $link = false;

    /**
     * 是否进行请求前确认
     * @var string
     */
    protected string $confirm = '';

    /**
     * 请求方法
     * @var string
     */
    protected string $method;

    /**
     * 请求的Url
     * @var string
     */
    protected string $url;

    /**
     * 标题/说明
     * @var string
     */
    protected string $title;

    /**
     * 渲染类型
     * @var string
     */
    protected string $renderType = 'link';

    /**
     * 流体按钮
     * @var bool
     */
    private bool $fluid = false;

    /**
     * 工具提示
     * @var string
     */
    private string $tooltip = '';

    /**
     * 创建 Action
     * @param $title
     * @param $url
     */
    public function __construct($title, $url)
    {
        $this->title     = $title;
        $this->url       = $url;
        $this->classes[] = 'layui-btn';
    }

    /**
     * 设置 ICON 图标, 图标支持 layui 图标, 图标使用 `lay:` 前缀
     * @param string $icon ICON 图标
     * @return $this
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function plain(string $color): self
    {
        if (in_array($color, [
            'green', 'blue', 'orange', 'red', 'black',
        ])) {
            $this->plain = $color;
        }
        return $this;
    }

    public function fluid(): self
    {
        $this->fluid = true;
        return $this;
    }

    /**
     * 确认
     * @param string $text
     * @return $this
     */
    public function confirm(string $text = ''): self
    {
        $this->confirm = $text;
        return $this;
    }

    public function tooltip(string $tooltip = ''): self
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function __call($method, $args)
    {
        if (in_array($method, [
            'primary', 'normal', 'warm', 'danger', 'disabled',
        ])) {
            $this->type = $method;
            return $this;
        }
        if (in_array($method, [
            'sm', 'xs', 'lg',
        ])) {
            $this->size = $method;
            return $this;
        }

        if (in_array($method, [
            'round', 'only',
        ])) {
            $this->$method = true;
            return $this;
        }
        return $this;
    }

    public function render(): string
    {
        // 风格
        $this->attributes['title'] = $this->title;
        if ($this->type) {
            if ($this->type === 'normal') {
                $this->classes['type'] = '';
            }
            else {
                $this->classes['type'] = 'layui-btn-' . $this->type;
            }
        }
        if ($this->plain) {
            $this->classes['type'] = 'layui-btn-primary';
            $this->classes[]       = 'layui-border-' . $this->plain;
        }
        if ($this->size) {
            $this->classes[] = 'layui-btn-' . $this->size;
        }
        if ($this->round) {
            $this->classes[] = 'layui-btn-radius';
        }
        if ($this->fluid) {
            $this->classes[] = 'layui-btn-fluid';
        }
        if ($this->tooltip) {
            $this->classes[]           = 'J_tooltip';
            $this->attributes['title'] = $this->tooltip;
        }

        $this->attributes['class'] = implode(' ', $this->classes);

        if ($this->confirm) {
            $this->attributes['data-confirm'] = $this->confirm;
        }

        $this->title = $this->createIconTitle();

        if ($this->renderType === 'link') {
            return Html::link($this->url, $this->title, $this->attributes, null, false)->toHtml();
        }

        return Form::button($this->title, $this->attributes)->toHtml();
    }

    /**
     * 创建图标标题
     * @return string
     */
    protected function createIconTitle(): string
    {
        $title = '';
        if ($this->icon) {
            $isLay = Str::contains($this->icon, 'lay:');
            if (!$isLay) {
                $icon = "<i class='fa fa-{$this->icon}'></i>";
            }
            else {
                $iconName = Str::after($this->icon, 'lay:');
                $icon     = "<i class='layui-icon layui-icon-{$iconName}'></i>";
            }
            if ($this->only) {
                $title = $icon;
            }
            else {
                $title = $icon . ' ' . $this->title;
            }
        }
        return $title;
    }
}
