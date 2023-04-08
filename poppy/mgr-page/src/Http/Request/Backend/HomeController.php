<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Auth;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Exceptions\PermissionException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\Framework\Helper\StrHelper;
use Poppy\MgrPage\Classes\Setting\SettingView;
use Poppy\MgrPage\Http\MgrPage\FormPassword;
use Poppy\System\Action\Pam;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Classes\Traits\UserSettingTrait;
use Poppy\System\Events\BePamLogoutEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;

/**
 * 主页控制器
 */
class HomeController extends BackendController
{
    use PoppyTrait, CoreTrait, UserSettingTrait;

    /**
     * 主页
     * @return View
     * @throws PermissionException
     */
    public function index(): View
    {
        $isFullPermission = $this->pam->hasRole(PamRole::BE_ROOT);
        $this->pyView()->share([
            '_menus' => $this->coreModule()->menus()->withPermission(PamAccount::TYPE_BACKEND, $isFullPermission, $this->pam),
        ]);
        $host = StrHelper::formatId(EnvHelper::host()) . '-backend';
        $name = sys_setting('py-system::site.name');
        $logo = sys_setting('py-system::site.logo');
        $main = route('py-mgr-page:backend.home.cp');
        return view('py-mgr-page::backend.home.index', [
            'host' => $host,
            'logo' => $logo,
            'name' => $name,
            'main' => $main,
        ]);
    }

    /**
     * 登录
     */
    public function login()
    {
        $auth     = $this->auth();
        $username = (string) input('username');
        $password = (string) input('password');
        $mobile   = (string) input('mobile');
        $code     = (string) input('code');

        if (is_post()) {
            $Pam = new Pam();
            try {
                $loginSuccess = false;
                if (!$username && !$mobile) {
                    return Resp::error('请输入通行证账号');
                }
                if ($username) {
                    if (config('poppy.mgr-page.captcha_login')) {
                        return Resp::error('请使用手机号+验证码登录');
                    }
                    if ($Pam->loginCheck($username, $password, PamAccount::GUARD_BACKEND)) {
                        $auth->login($Pam->getPam(), $this->isRemember());
                        $loginSuccess = true;
                    }
                }
                if ($mobile) {
                    if (!config('poppy.mgr-page.captcha_login')) {
                        return Resp::error('请使用通行证密码登录');
                    }
                    if ($Pam->beCaptchaLogin($mobile, $code)) {
                        $auth->login($Pam->getPam(), $this->isRemember());
                        $loginSuccess = true;
                    }
                }
                if ($loginSuccess) {
                    $this->setSessionLifetime($Pam->getPam());
                    $this->setRememberTokenExpired();
                    return Resp::success('登录成功', '_location|' . route('py-mgr-page:backend.home.index'));
                }
                return Resp::error($Pam->getError());
            } catch (ApplicationException $e) {
                return Resp::error($e->getMessage());
            }
        }

        if ($auth->check()) {
            return Resp::success('登录成功', '_location|' . route('py-mgr-page:backend.home.index'));
        }

        return view('py-mgr-page::backend.home.login');
    }

    /**
     * 修改本账户密码
     */
    public function password()
    {
        $form = new FormPassword();
        $form->setPam($this->pam);
        return $form->render();
    }

    public function clearCache()
    {
        $this->pyConsole()->call('poppy:optimize');
        return Resp::success('已清空缓存');
    }

    /**
     * 登出
     * @return JsonResponse|Response|RedirectResponse
     */
    public function logout()
    {
        $guard = Auth::guard(PamAccount::GUARD_BACKEND);

        $accountId = $guard->id();

        $guard->logout();

        event(new BePamLogoutEvent((int) $accountId));

        app('session.store')->flush();

        return Resp::success('退出登录', '_location|' . route('py-mgr-page:backend.home.login'));
    }

    /**
     * 控制面板
     * @return View
     */
    public function cp()
    {
        return view('py-mgr-page::backend.home.cp');
    }

    /**
     * Setting
     * @param string     $path 地址
     * @param int|string $index
     */
    public function setting(string $path = 'poppy.mgr-page', $index = 0)
    {
        return (new SettingView())->render($path, $index);
    }

    /**
     * tools
     * @param string $type 类型
     * @return Factory|View
     */
    public function easyWeb(string $type)
    {
        $host = StrHelper::formatId(EnvHelper::host());
        return view('py-mgr-page::backend.home.easyweb.' . $type, [
            'host' => $host,
        ]);
    }

    /**
     * 获取后台的Auth
     * @return Guard|SessionGuard
     */
    private function auth()
    {
        return Auth::guard(PamAccount::GUARD_BACKEND);
    }


    /**
     * 用户自定义的 Session 生命周期
     * @param PamAccount $pam
     */
    private function setSessionLifetime(PamAccount $pam): void
    {
        $defaultLoginHours = sys_setting('py-system::pam.lifetime') ?: 12;

        // 获取用户设定
        $setting  = $this->userSettingGet($pam->id, PySystemDef::uskAccount());
        $lifetime = ($setting['expired_hour'] ?? $defaultLoginHours) * 60;
        config(['session.lifetime' => $lifetime]);
    }

    /**
     * 是否记住了自动登录
     * @return bool
     */
    private function isRemember(): bool
    {
        return (bool) sys_setting('py-system::pam.is_remember');
    }

    /**
     * 设置记录登录时长的有效期
     * @return void
     */
    private function setRememberTokenExpired(): void
    {
        if (!$this->isRemember()) {
            return;
        }

        $auth        = $this->auth();
        $cookieJar   = $auth->getCookieJar();
        $cookieValue = $cookieJar->queued($auth->getRecallerName())->getValue();

        // reset expired value
        $rememberTokenExpireMinutes = ((int) sys_setting('py-system::pam.remember_hour', 60) ?: 60) * 24 * 60;
        $cookieJar->queue($auth->getRecallerName(), $cookieValue, $rememberTokenExpireMinutes);
    }
}