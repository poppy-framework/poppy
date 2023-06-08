<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Poppy\MgrPage\Classes\Form\Field;

class TableInput extends Field
{

    /**
     * @inheritDoc
     */
    protected $view = 'py-mgr-page::tpl.form.table_input';

    private array $table = [];

    /**
     * 设置表格数据
     * @param $table
     * @return $this
     */
    public function table($table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Render this filed.
     *
     * @return Factory|View
     */
    public function render()
    {
        $this->addVariables([
            'table' => $this->table,
        ]);

        return parent::render();
    }
}
