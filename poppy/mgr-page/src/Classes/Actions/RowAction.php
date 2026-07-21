<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Poppy\MgrPage\Classes\Grid\Column;

abstract class RowAction extends GridAction
{
    /**
     * @var string
     */
    public $selectorPrefix = '.grid-row-action-';

    /**
     * @var Model
     */
    protected $row;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var bool
     */
    protected $asColumn = false;

    /**
     * Set row model.
     *
     * @return Model|mixed
     */
    public function row($key = null)
    {
        if (0 == func_num_args()) {
            return $this->row;
        }

        return $this->row->getAttribute($key);
    }

    public function getRow()
    {
        return $this->row;
    }

    /**
     * Set row model.
     *
     * @param Model $row
     *
     * @return $this
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * @return $this
     */
    public function setColumn(Column $column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Show this action as a column.
     *
     * @return $this
     */
    public function asColumn()
    {
        $this->asColumn = true;

        return $this;
    }

    public function retrieveModel(Request $request)
    {
        if (!$key = $request->get('_key')) {
            return false;
        }

        $modelClass = str_replace('_', '\\', $request->get('_model'));

        if ($this->modelUseSoftDeletes($modelClass)) {
            return $modelClass::withTrashed()->findOrFail($key);
        }

        return $modelClass::findOrFail($key);
    }

    /**
     * Render row action.
     *
     * @return string
     */
    public function render()
    {
        $attributes = $this->formatAttributes();

        return sprintf(
            "<a data-_key='%s' href='javascript:void(0);' class='%s' {$attributes}>%s</a>",
            $this->getKey(),
            $this->getElementClass(),
            $this->asColumn ? $this->display($this->row($this->column->name)) : $this->name()
        );
    }

    /**
     * Get primary key value of current row.
     */
    protected function getKey()
    {
        return $this->row->getKey();
    }
}
