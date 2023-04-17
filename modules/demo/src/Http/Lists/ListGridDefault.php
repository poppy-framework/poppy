<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\ListBase;

class ListGridDefault extends ListBase
{

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        // 自定义样式
        $this->column('id', 'ID(排序)')->sortable()->width(100);

        // 开关
        $this->column('is_enable', 'Switch')->switch([
            1 => '打开',
            0 => '关闭',
        ]);

        // 文件链接地址
        $this->column('file', '文件链接地址')->link();

        $this->column('prefix', 'Prefix(默认前缀-Py)')->prefix('Py');

        $this->column('suffix', 'Suffix(默认后缀-Py)')->suffix('Py');
    }
}
