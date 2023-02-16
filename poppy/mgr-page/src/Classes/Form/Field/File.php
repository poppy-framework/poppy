<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Classes\Form\Field;

use Poppy\MgrPage\Classes\Form\Field;

class File extends Field
{
    public function image(): self
    {
        $this->options['type'] = 'images';
        return $this;
    }

    public function file(): self
    {
        $this->options['type'] = 'file';
        return $this;
    }

    public function audio(): self
    {
        $this->options['type'] = 'audio';
        return $this;
    }

    public function video(): self
    {
        $this->options['type'] = 'video';
        return $this;
    }

    /**
     * 自定义扩展
     * @param array $exts
     * @return File
     */
    public function exts(array $exts = []): self
    {
        $this->options['exts'] = $exts;
        return $this;
    }
}
