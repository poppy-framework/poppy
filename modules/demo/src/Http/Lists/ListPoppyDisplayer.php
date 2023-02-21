<?php

namespace Demo\Http\Lists;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\ListBase;

class ListPoppyDisplayer extends ListBase
{
    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', 'ID')->width(80);
        $this->column('title', '标题(可复制)')->width(150)->copyable();
        $this->column('image', '单个图片')->width(55)->image();
    }
}
