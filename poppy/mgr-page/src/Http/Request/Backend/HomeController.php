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
        if (is_post()) {
            $Pam = new Pam();
            try {
                if ($Pam->loginCheck($username, $password, PamAccount::GUARD_BACKEND)) {
                    $auth->login($Pam->getPam(), true);
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
        Auth::guard(PamAccount::GUARD_BACKEND)->logout();

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
        $Setting = new SettingView();
        return $Setting->render($path, $index);
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