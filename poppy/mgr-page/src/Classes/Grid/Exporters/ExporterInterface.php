<?php

namespace Poppy\MgrPage\Classes\Grid\Exporters;

interface ExporterInterface
{
    /**
     * Export data from grid.
     */
    public function export();
}
