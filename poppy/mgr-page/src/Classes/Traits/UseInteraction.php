<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Traits;

use Closure;
use Poppy\MgrPage\Classes\Operation\CopyOperation;
use Poppy\MgrPage\Classes\Operation\DropdownOperation;
use Poppy\MgrPage\Classes\Operation\IframeOperation;
use Poppy\MgrPage\Classes\Operation\PageOperation;
use Poppy\MgrPage\Classes\Operation\RequestOperation;
use Poppy\MgrPage\Classes\Operation\ToolbarOperation;

trait UseInteraction
{

    /**
     * 请求
     * @param string $title
     * @param string $url
     * @return IframeOperation
     */
    public function iframe(string $title, string $url): IframeOperation
    {
        $action = new IframeOperation($title, $url);
        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }


    /**
     * 复制
     * @param string $title
     * @param        $content
     * @return CopyOperation
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
     * @param string $title
     * @param string $url
     * @return RequestOperation
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
     * @param string $title
     * @param string $url
     * @return ToolbarOperation
     */
    public function toolbar(string $title, string $url): ToolbarOperation
    {
        $action = new ToolbarOperation($title, $url);
        return tap($action, function () use ($action) {
            $this->add($action);
        });
    }

    /**
     * 页面
     * @param string $title
     * @param string $url
     * @return PageOperation
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
     * @param string  $title
     * @param Closure $callable
     * @return DropdownOperation
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
