<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Grid\Filter\Presenter;

class Text extends Presenter
{
    protected string $placeholder = '';

    protected string $icon = 'pencil';

    protected string $type = 'text';

    /**
     * Text constructor.
     */
    public function __construct(string $placeholder = '')
    {
        $this->placeholder($placeholder);
    }

    /**
     * Get variables for field template.
     */
    public function variables(): array
    {
        return [
            'placeholder' => $this->placeholder,
            'icon'        => $this->icon,
            'type'        => $this->type,
            'group'       => $this->filter->group,
        ];
    }

    /**
     * Set input placeholder.
     *
     * @return $this
     */
    public function placeholder(string $placeholder = ''): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function url(): self
    {
        return $this->inputmask(['alias' => 'url'], 'internet-explorer');
    }

    public function email(): self
    {
        return $this->inputmask(['alias' => 'email'], 'envelope');
    }

    public function integer(): self
    {
        return $this->inputmask(['alias' => 'integer']);
    }

    /**
     * @param array $options
     *
     * @see https://github.com/RobinHerbots/Inputmask/blob/4.x/README_numeric.md
     */
    public function decimal($options = []): self
    {
        return $this->inputmask(array_merge($options, ['alias' => 'decimal']));
    }

    /**
     * @param array $options
     *
     * @see https://github.com/RobinHerbots/Inputmask/blob/4.x/README_numeric.md
     */
    public function currency($options = []): self
    {
        return $this->inputmask(array_merge($options, [
            'alias'              => 'currency',
            'prefix'             => '',
            'removeMaskOnSubmit' => true,
        ]));
    }

    /**
     * @param array $options
     *
     * @return Text
     *
     * @see https://github.com/RobinHerbots/Inputmask/blob/4.x/README_numeric.md
     */
    public function percentage($options = [])
    {
        $options = array_merge(['alias' => 'percentage'], $options);

        return $this->inputmask($options);
    }

    public function ip(): self
    {
        return $this->inputmask(['alias' => 'ip'], 'laptop');
    }

    public function mac(): self
    {
        return $this->inputmask(['alias' => 'mac'], 'laptop');
    }

    public function mobile(string $mask = '19999999999'): self
    {
        return $this->inputmask(compact('mask'), 'phone');
    }

    /**
     * @param array  $options
     * @param string $icon
     *
     * @return $this
     */
    public function inputmask($options = [], $icon = 'pencil'): self
    {
        $this->icon = $icon;

        return $this;
    }
}
