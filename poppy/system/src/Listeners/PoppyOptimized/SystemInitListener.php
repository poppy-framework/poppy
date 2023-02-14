<?php

declare(strict_types = 1);

namespace Poppy\System\Listeners\PoppyOptimized;

use Poppy\Framework\Events\PoppyOptimized;
use Poppy\System\Action\Ban;

/**
 * 系统初始化
 */
class SystemInitListener
{

    /**
     * @param PoppyOptimized $event 框架优化
     */
    public function handle(PoppyOptimized $event): void
    {
        // init sso

        // init ban
        (new Ban())->initCache();
    }
}

