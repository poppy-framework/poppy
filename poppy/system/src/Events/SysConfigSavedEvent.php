<?php
declare(strict_types = 1);

namespace Poppy\System\Events;

use Poppy\System\Models\SysConfig;

/**
 * SysConfig 表创建或更新事件
 */
class SysConfigSavedEvent
{
    public SysConfig $config;

    public function __construct(SysConfig $sysConfig)
    {
        $this->config = $sysConfig;
    }
}