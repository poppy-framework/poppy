<?php

namespace Poppy\MgrApp\Classes\Form\Field;

use Closure;
use Illuminate\Support\Arr;
use Poppy\MgrApp\Classes\Form\FormItem;

class Html extends FormItem
{
    /**
     * Htmlable.
     *
     * @var string|Closure
     */
    protected $html = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var bool
     */
    protected $plain = false;

    /**
     * Create a new Html instance.
     *
     * @param mixed $html
     * @param array $arguments
     */
    public function __construct($html, $arguments)
    {
        $this->html = $html;

        $this->label = Arr::get($arguments, 0);
    }

    /**
     * @return $this
     */
    public function plain()
    {
        $this->plain = true;

        return $this;
    }

    /**
     * Render html field.
     *
     * @return string
     */
    public function render()
    {
        return $this->html;
    }
}
