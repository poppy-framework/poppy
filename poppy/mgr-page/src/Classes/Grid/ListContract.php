<?php

namespace Poppy\MgrPage\Classes\Grid;

use Closure;

interface ListContract
{
    /**
     * 添加列展示
     * @return mixed
     */
    public function columns();

    /**
     * 添加搜索项
     * @return Closure
     */
    public function filter(): Closure;

    /**
     * 添加操作项目, 合并到列处理¶
     * @return mixed
     * @see        columns()
     * @deprecated 4.2
     */
    public function actions();

    /**
     * 批量操作
     * @return array|Closure
     */
    public function batchAction();


    /**
     * 定义右上角的快捷操作栏
     * @return array|Closure
     */
    public function quickButtons();
}
