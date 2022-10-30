<?php

namespace Demo\Listeners\PassportVerify;

use Illuminate\Support\Str;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Events\PassportVerifyEvent;
use Poppy\System\Models\PamAccount;

/**
 * 手机号验证
 */
class PassportVerifyListener
{
    /**
     * Handle the event.
     * @param PassportVerifyEvent $event
     * @return void
     * @throws ApplicationException
     */
    public function handle(PassportVerifyEvent $event)
    {
        $passport = $event->passport;
        $type     = $event->type;
        if (!$type) {
            return;
        }

        if ($type) {
            if ($type !== 'exist') {
                throw new ApplicationException('类型错误');
            }

            if (!Str::contains($passport, '-')) {
                $passport = '86-' . $passport;
            }
            if (!PamAccount::where('mobile', $passport)->exists()) {
                throw new ApplicationException('系统不存在此手机号!');
            }
        }

    }
}
