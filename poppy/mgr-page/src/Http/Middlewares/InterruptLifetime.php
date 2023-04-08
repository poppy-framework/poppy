<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Classes\Traits\UserSettingTrait;
use Poppy\System\Models\PamAccount;

/**
 * 修改登录凭证的有效期
 */
class InterruptLifetime
{
    use UserSettingTrait;

    /**
     * Middleware handler.
     * @param Request $request request
     * @param Closure $next    next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($user = $request->user()) {
            /** @var PamAccount $user */
            $setting  = $this->userSettingGet($user->id, PySystemDef::uskAccount());
            $lifetime = ($setting['expired_hour'] ?? 12) * 60;
            config(['session.lifetime' => $lifetime]);
        }
        return $next($request);
    }
}