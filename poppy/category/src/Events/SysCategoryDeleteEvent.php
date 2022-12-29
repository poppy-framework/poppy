<?php

declare(strict_types = 1);

namespace Poppy\Category\Events;

use Poppy\Category\Models\SysCategory;
use Poppy\Framework\Application\Event;

class SysCategoryDeleteEvent extends Event
{
    public SysCategory $category;

    public function __construct(SysCategory $category)
    {
        $this->category = $category;
    }
}
