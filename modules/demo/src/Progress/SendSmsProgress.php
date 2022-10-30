<?php

namespace Demo\Progress;

use Poppy\System\Classes\Progress\BaseProgress;
use Poppy\System\Classes\Traits\FixTrait;

/**
 * Demo Send Sms
 */
class SendSmsProgress extends BaseProgress
{
    use FixTrait;

    public function handle(): array
    {

        // 设置执行值
        $this->max(2000);
        $this->min(1);
        $this->start(1);
        $this->cached(true);
        $this->interval(200);
        $this->section(90);

        // 设置显示值
        $this->total(2000);
        $left = $this->total() - $this->start();
        $this->left($left > 0 ? $left : 0, true);

        if ($this->left() > 0) {
            // do progress

            // 更新 lastId
            $this->lastId($this->start() + $this->section(), true);
        }
        return $this->fix;
    }
}