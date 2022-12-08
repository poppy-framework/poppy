<?php

declare(strict_types = 1);

namespace Poppy\Area\Listeners\PoppyOptimized;

use Poppy\Framework\Events\PoppyOptimized;

/**
 * 清除缓存
 */
class ClearCacheListener
{

    /**
     * @param PoppyOptimized $event 框架优化
     */
    public function handle(PoppyOptimized $event)
    {
        sys_tag('py-area')->clear();
    }
}

