<?php

declare(strict_types = 1);

namespace Poppy\System\Classes\Captcha;

interface RequestThrottleService
{
    /**
     * 请求限流
     * 如果验证通过返回true，否则返回false
     */
    public function throttle(): bool;
}
