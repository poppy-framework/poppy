<?php

declare(strict_types = 1);

namespace Poppy\Content\Events;

use Poppy\Content\Models\SysContent;
use Poppy\Framework\Application\Event;

class SysContentDeleteEvent extends Event
{
    public SysContent $category;

    public function __construct(SysContent $category)
    {
        $this->category = $category;
    }
}
