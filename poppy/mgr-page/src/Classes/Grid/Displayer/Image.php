<?php

namespace Poppy\MgrPage\Classes\Grid\Displayer;

use Illuminate\Contracts\Support\Arrayable;
use Poppy\System\Classes\File\FileManager;

class Image extends AbstractDisplayer
{
    public function display($width = 20, $height = 20)
    {
        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        }

        return collect((array) $this->value)->filter()->map(function ($path) use ($width, $height) {
            $url = FileManager::previewImage($path, $width);

            return "<img src='$url'
            data-src='$path'
            style='max-width:{$width}px;max-height:{$height}px' class='J_image_preview' />";
        })->implode('&nbsp;');
    }
}
