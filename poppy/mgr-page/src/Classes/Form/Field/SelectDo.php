<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Illuminate\Support\Str;

class SelectDo extends Select
{

    public function __construct($column = '', $arguments = [])
    {
        parent::__construct($column, $arguments);
        $this->attribute([
            'lay-filter' => 'lay-select-do-' . Str::random(8),
        ]);
    }

    /**
     * 选择之后去做的操作, 操作之间是互斥的
     * @param string $url
     * @param string $param
     * @return $this
     */
    public function location(string $url = '', string $param = ''): self
    {
        $urlPrefix = $url;
        $urlSuffix = '';
        if (Str::contains($url, '?')) {
            $urlPrefix = Str::before($url, '?');
            $urlSuffix = Str::after($url, '?');
        }
        if (!$param && is_string($this->column)) {
            $param = (string) $this->column;
        }
        if (Str::contains($urlSuffix, '&')) {
            $urlSuffix .= '&' . $param . '=';
        }
        else {
            $urlSuffix .= $param . '=';
        }
        $this->addVariables([
            'type' => 'location',
            'url'  => $urlPrefix . '?' . $urlSuffix,
        ]);
        return $this;
    }
}
