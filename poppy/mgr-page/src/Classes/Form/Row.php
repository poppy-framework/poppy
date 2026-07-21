<?php

namespace Poppy\MgrPage\Classes\Form;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Poppy\MgrPage\Classes\Form;

class Row implements Renderable
{
    /**
     * Callback for add field to current row.s.
     *
     * @var Closure
     */
    protected $callback;

    /**
     * Parent form.
     *
     * @var Form
     */
    protected $form;

    /**
     * Fields in this row.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Default field width for appended field.
     *
     * @var int
     */
    protected $defaultFieldWidth = 12;

    /**
     * Row constructor.
     */
    public function __construct(Closure $callback, Form $form)
    {
        $this->callback = $callback;

        $this->form = $form;

        call_user_func($this->callback, $this);
    }

    /**
     * Get fields of this row.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set width for an incomming field.
     *
     * @param int $width
     *
     * @return $this
     */
    public function width($width = 12)
    {
        $this->defaultFieldWidth = $width;

        return $this;
    }

    /**
     * Render the row.
     *
     * @return Factory|View
     */
    public function render()
    {
        return view('py-mgr-page::tpl.form.row', ['fields' => $this->fields]);
    }

    /**
     * Add field.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return Field|void
     */
    public function __call($method, $arguments)
    {
        $field = $this->form->__call($method, $arguments);

        $field->disableHorizontal();

        $this->fields[] = [
            'width'   => $this->defaultFieldWidth,
            'element' => $field,
        ];

        return $field;
    }
}
