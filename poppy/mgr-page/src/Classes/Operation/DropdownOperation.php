<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Operation;

use Closure;
use Poppy\MgrPage\Classes\Operations;

/**
 * 下拉菜单ø
 */
final class DropdownOperation extends Operation
{
    protected Closure $callable;

    private string $color = 'secondary';

    /**
     * 预览
     *
     * @return void
     */
    public function operations(Closure $callable): self
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * 设定显示颜色
     *
     * @return $this
     */
    public function color(string $type = 'secondary'): self
    {
        if (in_array($type, [
            'primary', 'secondary', 'success', 'info', 'warning', 'danger',
        ])) {
            $this->color = $type;
        }

        return $this;
    }

    public function render(): string
    {
        $Operations = new Operations();
        call_user_func($this->callable, $Operations);
        $items   = $Operations->items();
        $content = '';
        if (count($items)) {
            foreach ($items as $item) {
                $item->classes[] = 'dropdown-item';
                $content .= '<li>' . $item->render() . '</li>';
            }
        }

        $title = $this->createIconTitle();

        return <<<HTML
<div class="btn-group btn-group-xs">
  <button class="btn btn-{$this->color} dropdown-toggle" type="button" data-bs-toggle="dropdown">
    {$title}
  </button>
  <ul class="dropdown-menu">
    {$content}
  </ul>
</div>
HTML;
    }
}
