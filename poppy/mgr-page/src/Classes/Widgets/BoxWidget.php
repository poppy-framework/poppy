<?php

namespace Poppy\MgrPage\Classes\Widgets;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Poppy\MgrPage\Classes\Operations;
use Throwable;

class BoxWidget extends Widget implements Renderable
{

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $content = 'here is the box content.';

    /**
     * @var string
     */
    protected $footer = '';

    /**
     * @var ?Operations
     */
    protected ?Operations $tools = null;

    /**
     * Box constructor.
     *
     * @param string $title
     * @param string $content
     */
    public function __construct($title = '', string $content = '')
    {
        parent::__construct();
        if ($title) {
            $this->title($title);
        }

        if ($content) {
            $this->content($content);
        }

        $this->tools = new Operations();
    }

    /**
     * Set box content.
     *
     * @param string|Renderable $content
     *
     * @return $this
     */
    public function content($content): self
    {
        if ($content instanceof Renderable) {
            $this->content = $content->render();
        }
        else {
            $this->content = $content;
        }

        return $this;
    }

    /**
     * Set box title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Render box.
     * @throws Throwable
     */
    public function render()
    {
        return view('py-mgr-page::tpl.widgets.box', $this->variables())->render();
    }

    /**
     * 右上角工具栏
     * @return $this
     */
    public function tools(Closure $closure = null): self
    {
        if (!is_null($closure)) {
            $closure($this->tools);
        }
        return $this;
    }

    /**
     * Variables in view.
     *
     * @return array
     */
    protected function variables(): array
    {
        return [
            'title'   => $this->title,
            'content' => $this->content,
            'tools'   => $this->tools,
        ];
    }
}
