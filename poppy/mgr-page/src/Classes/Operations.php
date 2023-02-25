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
        $this->iframe($title, $url)->icon('lay:addition')->sm();
    }

    /**
     * 设置
     * @param string $url
     * @param string $title
     * @return void
     */
    public function setting(string $url, string $title = '设置'): void
    {
        $this->iframe($title, $url)->icon('lay:set')->sm();
    }

    public function download(string $url, string $title = '下载', $tooltip = ''): void
    {
        $this->page($title, $url)->icon('lay:download-circle')->sm()->tooltip($tooltip);
    }

    /**
     * 工具栏删除
     * @param $url
     * @return void
     */
    public function toolbarDelete($url): void
    {
        $this->toolbar('批量删除', $url)->icon('lay:close')->danger()
            ->confirm("确认删除选中数据 ?")->sm();
    }

    /**
     * 批次更新
     * @param string $url
     * @return void
     */
    public function progress(string $url): void
    {
        $this->iframe('更新', $url)->icon('lay:chart')->sm();
    }

    /**
     * 禁用
     * @param string $url
     * @param string $title
     * @return void
     */
    public function disable(string $url, string $title): void
    {
        $this->request('已启用', $url)->icon('lay:ok-circle')
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
        $this->request('已禁用', $url)->icon('lay:pause')
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
