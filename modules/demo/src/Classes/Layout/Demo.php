<?php

namespace Demo\Classes\Layout;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Poppy\Framework\Exceptions\PoppyException;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\Framework\Helper\StrHelper;
use Throwable;

class Demo implements Renderable
{
    /**
     * Content title.
     */
    protected string $title = '';

    /**
     * Content description.
     */
    protected string $description = '';

    /**
     * Content constructor.
     *
     * @throws PoppyException
     */
    public function __construct(?Closure $callback = null)
    {
        if (!app('poppy')->exists('poppy.mgr-page')) {
            throw new PoppyException('模块 `poppy.mgr-page` 不存在');
        }
        if ($callback instanceof Closure) {
            $callback($this);
        }
    }

    /**
     * @return $this
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set description of content.
     *
     * @return $this
     */
    public function description(string $description = ''): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Render this content.
     *
     * @throws Throwable
     */
    public function render()
    {
        $menus     = app('poppy.core.module')->menus()->withType('demo', []);
        $variables = [
            '_title'       => $this->title,
            '_description' => $this->description,
            'host'         => StrHelper::formatId(EnvHelper::host()) . '-demo',
            '_menus'       => $menus,
        ];

        // 这里的显示依赖于 py-mgr-page
        // 因为只是后台, 所以提取一个给后边用
        return view('demo::backend.tpl.demo', $variables)->render();
    }
}
