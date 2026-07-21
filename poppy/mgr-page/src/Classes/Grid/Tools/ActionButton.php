<?php

namespace Poppy\MgrPage\Classes\Grid\Tools;

use Illuminate\Support\Str;

/**
 * 创建按钮
 *
 * @deprecated 4.2
 *
 * @removed    5.0
 */
class ActionButton
{
    protected $title;

    protected $url;

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
        $class .= ' J_request ';

        $this->attribute['class']     = $class;
        $this->attribute['data-url']  = $url;
        $this->attribute['lay-event'] = Str::random(4);
    }

    /**
     * Render CreateButton.
     */
    public function render(): string
    {
        return ' ' . app('poppy.mgr-page.form')->button($this->title, $this->attribute) . ' ';
    }
}
