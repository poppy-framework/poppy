<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes;

use Illuminate\Contracts\Support\Renderable;
use Poppy\MgrPage\Classes\Operation\Operation;
use Poppy\MgrPage\Classes\Traits\UseInteraction;
use Poppy\MgrPage\Classes\Traits\UseItems;

class Operations implements Renderable
{

    use UseItems, UseInteraction;

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $actions = [];
        foreach ($this->items as $append) {
            if ($append instanceof Operation) {
                $actions[] = $append->render();
            }
        }

        return implode('', $actions);
    }


    /**
     * 新建
     * @param string $url
     * @param string $title
     * @return void
     */
    public function create(string $url, string $title = '新建'): void
    {
        $this->iframe($title, $url)->icon('plus-circle')->sm();
    }


    /**
     * 编辑
     * @param string $url
     * @param string $title
     * @return void
     */
    public function edit(string $url, string $title = '编辑'): void
    {
        $this->iframe($title, $url)->icon('pencil')->xs();
    }

    /**
     * 删除
     * @param string $url
     * @param string $confirm
     * @param string $title
     * @return void
     */
    public function delete(string $url, string $confirm = '', string $title = '删除'): void
    {
        $this->iframe($title, $url)->confirm($confirm)->icon('trash')->danger()->xs();
    }

    /**
     * 设置
     * @param string $url
     * @param string $title
     * @return void
     */
    public function setting(string $url, string $title = '设置'): void
    {
        $this->iframe($title, $url)->icon('sliders')->sm();
    }


    /**
     * 下载
     * @param string $url
     * @param string $title
     * @param string $tooltip
     * @return void
     */
    public function download(string $url, string $title = '下载', string $tooltip = ''): void
    {
        $this->page($title, $url)->icon('download')->sm()->tooltip($tooltip);
    }

    /**
     * 工具栏删除
     * @param $url
     * @return void
     */
    public function toolbarDelete($url): void
    {
        $this->toolbar('批量删除', $url)->icon('trash')->danger()
            ->confirm("确认删除选中数据 ?")->sm();
    }

    /**
     * 批次更新
     * @param string $url
     * @return void
     */
    public function progress(string $url): void
    {
        $this->iframe('更新', $url)->icon('columns-gap')->sm();
    }

    /**
     * 禁用
     * @param string $url
     * @param string $title
     * @return void
     */
    public function disable(string $url, string $title): void
    {
        $this->request('已启用', $url)->icon('check-circle')
            ->confirm("确定要禁用 [{$title}]")->tooltip("当前启用, 点击禁用 [{$title}]")->sm();
    }

    /**
     * 启用
     * @param string $url
     * @param string $title
     * @return void
     */
    public function enable(string $url, string $title): void
    {
        $this->request('已禁用', $url)->icon('slash-circle')
            ->confirm("确定启用 [{$title}]")->tooltip("当前禁用, 点击启用 [{$title}]")->danger()->sm();
    }

    /**
     * @return array|Operation[]
     */
    public function items(): array
    {
        return $this->items;
    }
}
