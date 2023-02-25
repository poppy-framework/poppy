<?php

namespace Poppy\MgrPage\Classes\Grid\Concerns;

use JsonException;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\MgrPage\Classes\Grid\Column;

/**
 * Layui 的参数定义
 */
trait LayDefines
{
    use PoppyTrait;

    /**
     * @var array
     */
    protected $layElem = '';

    protected $layCols = [[]];

    /**
     * 全局定义常规单元格的最小宽度，layui 2.2.1 新增
     * @var int
     */
    protected $layCellMinWidth = 80;


    protected $layPage = true;

    /**
     * 增加行选择器
     */
    protected function layPrependRowSelectorColumn()
    {
        array_unshift($this->layCols[0], ['type' => 'checkbox']);
    }


    /**
     * Layui 格式化
     */
    protected function layFormat()
    {
        $this->layElem = $this->tableId;
    }


    /**
     * 列样式
     * @url https://www.layui.com/doc/modules/table.html#skin
     */
    protected function layColumns()
    {
        $columns = [];
        collect($this->visibleColumns())->each(function (Column $column) use (&$columns) {
            $columns[] = $column->lay();
        });
        $this->layCols[0] = array_merge($this->layCols[0], $columns);
    }

    /**
     * 定义 Layui 的数据定义
     * @return string
     */
    protected function layDefine(): string
    {
        // 计算 Column For LayCols
        $this->layColumns();
        try {
            return json_encode([
                'elem'         => '#' . $this->layElem,
                'url'          => $this->pyRequest()->fullUrlWithQuery([]),
                'where'        => [
                    '_query' => 1,
                ],
                'cols'         => $this->layCols,
                'page'         => $this->layPage,
                'limits'       => $this->perPages,
                'cellMinWidth' => $this->layCellMinWidth,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (JsonException $e) {
            return '';
        }
    }
}
