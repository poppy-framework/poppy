<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Traits;

use Closure;
use Poppy\MgrPage\Classes\Operation\BatchIframeOperation;
use Poppy\MgrPage\Classes\Operation\BatchRequestOperation;
use Poppy\MgrPage\Classes\Operation\CopyOperation;
use Poppy\MgrPage\Classes\Operation\DropdownOperation;
use Poppy\MgrPage\Classes\Operation\IframeOperation;
use Poppy\MgrPage\Classes\Operation\LoadViewOperation;
use Poppy\MgrPage\Classes\Operation\PageOperation;
use Poppy\MgrPage\Classes\Operation\RequestOperation;

trait UseInteraction
{
    /**
     * 地址弹窗
     */
    public function iframe(string $title, string $url): IframeOperation
    {
        $action = new IframeOperation($title, $url);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 加载Tab
     */
    public function loadView(string $title, string $url): LoadViewOperation
    {
        $action = new LoadViewOperation($title, $url);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 复制
     */
    public function copy(string $title, $content): CopyOperation
    {
        $action = new CopyOperation($title, $content);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 请求
     */
    public function request(string $title, string $url): RequestOperation
    {
        $action = new RequestOperation($title, $url);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 请求
     *
     * @deprecated
     * @see batchRequest
     */
    public function toolbar(string $title, string $url): BatchRequestOperation
    {
        return $this->batchRequest($title, $url);
    }

    /**
     * 请求
     */
    public function batchRequest(string $title, string $url): BatchRequestOperation
    {
        $action = new BatchRequestOperation($title, $url);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 请求
     */
    public function batchIframe(string $title, string $url): BatchIframeOperation
    {
        $action = new BatchIframeOperation($title, $url);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 页面
     */
    public function page(string $title, string $url): PageOperation
    {
        $action = (new PageOperation($title, $url));

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 下拉操作项
     */
    public function dropdown(string $title, Closure $callable): DropdownOperation
    {
        $action = (new DropdownOperation($title, ''));
        $action->operations($callable);

        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }
}
