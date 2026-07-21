<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Exceptions\PermissionException;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Classes\Traits\PoppyTrait;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Helper\EnvHelper;
use Poppy\Framework\Helper\StrHelper;
use Poppy\Framework\Helper\UtilHelper;
use Poppy\MgrPage\Classes\Setting\SettingView;
use Poppy\MgrPage\Http\MgrPage\FormPassword;
use Poppy\System\Action\Pam;
use Poppy\System\Classes\Traits\UserSettingTrait;
use Poppy\System\Events\BePamLogoutEvent;
use Poppy\System\Http\Validation\PamLoginRequest;
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
     *
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
        $main = route('py-mgr-page:backend.home.cp', [], false);

        return view('py-mgr-page::backend.home.index', [
            'host' => $host,
            'logo' => $logo,
            'name' => $name,
            'main' => $main,
        ]);
    }

    /**
     * 登录
     *
     * @throws ApplicationException
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function login(Request $req)
    {
        $Pam  = new Pam();
        $auth = $Pam->auth();
        $req->merge([
            'os' => PamAccount::REG_PLATFORM_MGR,
        ]);

        if (is_post()) {
            /** @var PamLoginRequest $request */
            $request      = app(PamLoginRequest::class, [$req]);
            $reqPassport  = $request->scene('passport')->validated();
            $isMobile     = UtilHelper::isMobile($reqPassport['passport']);
            $loginSuccess = false;
            if (!$isMobile) {
                $reqPwd = $request->scene('password')->validated();
                if ($Pam->loginCheck($reqPwd['passport'], $reqPwd['password'], PamAccount::GUARD_BACKEND)) {
                    $auth->login($Pam->getPam(), $Pam->isRemember());
                    $loginSuccess = true;
                }
            }
            if ($isMobile) {
                $reqCaptcha = $request->scene('captcha')->validated();
                if ($Pam->beCaptchaLogin($reqCaptcha['passport'], $reqCaptcha['captcha'])) {
                    $auth->login($Pam->getPam(), $Pam->isRemember());
                    $loginSuccess = true;
                }
            }
            if ($loginSuccess) {
                $Pam->setSessionLifetime($Pam->getPam());
                $Pam->setRememberTokenExpired();

                return Resp::success('登录成功', '_location|' . route('py-mgr-page:backend.home.index'));
            }

            return Resp::error($Pam->getError());
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
     *
     * @return JsonResponse|Response|RedirectResponse
     */
    public function logout()
    {
        $guard = Auth::guard(PamAccount::GUARD_BACKEND);

        $accountId = $guard->id();

        $guard->logout();

        event(new BePamLogoutEvent((int) $accountId));

        // todo 退出后台清空 session 会导致其他用户失效
        app('session.store')->flush();

        return Resp::success('退出登录', '_location|' . route('py-mgr-page:backend.home.login'));
    }

    /**
     * 控制面板
     *
     * @return View
     */
    public function cp()
    {
        return view('py-mgr-page::backend.home.cp');
    }

    /**
     * Setting
     *
     * @param string     $path  地址
     * @param int|string $index
     */
    public function setting(string $path = 'poppy.mgr-page', $index = 0)
    {
        return (new SettingView())->render($path, $index);
    }

    /**
     * tools
     *
     * @param string $type 类型
     *
     * @return Factory|View
     */
    public function easyWeb(string $type)
    {
        $host = StrHelper::formatId(EnvHelper::host());

        return view('py-mgr-page::backend.home.easyweb.' . $type, [
            'host' => $host,
        ]);
    }
}
