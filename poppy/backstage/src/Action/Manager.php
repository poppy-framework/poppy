<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Action;

use Auth;
use Carbon\Carbon;
use Poppy\Backstage\Http\Validation\MgrLoginRequest;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Helper\StrHelper;
use Poppy\System\Action\Verification;
use Poppy\System\Classes\Traits\PamTrait;
use Poppy\System\Classes\Traits\UserSettingTrait;
use Poppy\System\Events\LoginBannedEvent;
use Poppy\System\Events\LoginFailedEvent;
use Poppy\System\Events\LoginSuccessEvent;
use Poppy\System\Events\PamEnableEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;
use Throwable;
use Tymon\JWTAuth\JWTGuard;

/**
 * 独立出来管理员登录, 支持 JWT Token
 */
class Manager
{
    use UserSettingTrait, AppTrait, PamTrait;


    /**
     * 后台验证码登录
     * @param MgrLoginRequest $request
     * @return bool
     */
    public function captchaLogin(MgrLoginRequest $request): bool
    {

        $mobile = $request->getPassport();
        // 验证账号 + 验证码
        $verification = new Verification();

        if (!$verification->checkCaptcha($mobile, $request->getCaptcha())) {
            return $this->setError($verification->getError()->getMessage());
        }

        // 判定账号是否存在
        $mobile    = $this->fillMobile($mobile);
        $this->pam = PamAccount::where('mobile', $mobile)->firstOrFail();

        // 检测权限, 是否被禁用
        if (!$this->checkIsEnable($this->pam)) {
            return false;
        }

        try {
            event(new LoginBannedEvent($this->pam, PamAccount::GUARD_BACKEND));
        } catch (Throwable $e) {
            return $this->setError($e);
        }

        event(new LoginSuccessEvent($this->pam, PamAccount::GUARD_BACKEND));
        return true;
    }

    /**
     * 密码登录
     * @param MgrLoginRequest $request
     * @return bool
     */
    public function loginCheck(MgrLoginRequest $request): bool
    {
        $passport    = PamAccount::fullFilledPassport($request->getPassport());
        $password    = $request->getPassword();
        $type        = PamAccount::passportType($passport);
        $credentials = [
            $type      => $passport,
            'password' => $password,
        ];

        $guardName = PamAccount::GUARD_JWT_BACKEND;

        /** @var JWTGuard $guard */
        $guard = Auth::guard($guardName);

        if ($guard->attempt($credentials)) {

            $this->pam = $guard->user();

            if (!$this->checkIsEnable($this->pam)) {
                return false;
            }

            try {
                event(new LoginBannedEvent($this->pam, $guardName));
            } catch (Throwable $e) {
                return $this->setError($e);
            }

            event(new LoginSuccessEvent($this->pam, $guardName));
            return true;
        }

        $credentials = array_merge($credentials, [
            'type'     => $type,
            'passport' => $passport,
            'password' => StrHelper::hideContact($password)
        ]);

        event(new LoginFailedEvent($credentials));

        return $this->setError(trans('py-system::action.pam.login_fail_again'));

    }

    /**
     * 验证用户权限
     * @param PamAccount $pam 用户
     * @return bool
     */
    private function checkIsEnable(PamAccount $pam): bool
    {
        if ($pam->is_enable === SysConfig::NO) {
            $now = Carbon::now();
            // 当前时间大于禁用时间(已解禁)
            if ($now->gt($pam->disable_end_at)) {
                $pam->is_enable = SysConfig::ENABLE;
                $pam->save();
                event(new PamEnableEvent($pam, $this->pam, '用户登录, 超过封禁时间, 自动解禁'));
                return true;
            }
            return $this->setError("该账号因 $pam->disable_reason 被封禁至 $pam->disable_end_at");
        }
        return true;
    }


    /**
     * 完善手机号
     * @param string $mobile
     * @return string
     */
    private function fillMobile(string $mobile): string
    {
        return PamAccount::BACKEND_MOBILE_PREFIX . $mobile;
    }
}
