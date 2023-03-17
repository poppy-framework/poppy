<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
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
use Poppy\System\Events\BePamLogoutEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;

/**
 * 主页控制器
 */
class HomeController extends BackendController
{
    use PoppyTrait, CoreTrait;

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
                if ($username) {
                    if (config('poppy.mgr-page.captcha_login')) {
                        return Resp::error('请使用手机号+验证码登录');
                    }
                    if ($Pam->loginCheck($username, $password, PamAccount::GUARD_BACKEND)) {
                        $auth->login($Pam->getPam(), true);
                        return Resp::success('登录成功', '_location|' . route('py-mgr-page:backend.home.index'));
                    }
                    return Resp::error($Pam->getError());
                }
                if ($mobile) {
                    if (!config('poppy.mgr-page.captcha_login')) {
                        return Resp::error('请使用通行证密码登录');
                    }
                    if ($Pam->beCaptchaLogin($mobile, $code)) {
                        $auth->login($Pam->getPam(), true);
                        return Resp::success('登录成功', '_location|' . route('py-mgr-page:backend.home.index'));
                    }
                    return Resp::error($Pam->getError());
                }

                return Resp::error('请输入通行证账号');
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
        sys_tag('py-core')->clear();
        sys_tag('py-system')->clear();
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

        app('session.store')->flush();

        event(new BePamLogoutEvent((int) $accountId));

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
     * @param null|string $type 类型
     * @return Factory|View
     */
    public function easyWeb($type = null)
    {
        $host = StrHelper::formatId(EnvHelper::host());
        return view('py-mgr-page::backend.home.easyweb.' . $type, [
            'host' => $host,
        ]);
    }

    /**
     * 获取后台的Auth
     * @return Guard|StatefulGuard
     */
    private function auth()
    {
        return Auth::guard(PamAccount::GUARD_BACKEND);
    }
}