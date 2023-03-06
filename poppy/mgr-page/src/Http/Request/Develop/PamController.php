<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

use Auth;
use Poppy\Framework\Classes\Resp;
use Poppy\System\Action\Pam;
use Poppy\System\Models\PamAccount;

/**
 * 开发平台用户登录控制器
 */
class PamController extends DevelopController
{
    /**
     * 登录
     */
    public function login()
    {
        if (is_post()) {
            $username = input('username');
            $password = input('password');

            $pam = new Pam();
            if ($pam->loginCheck($username, $password, PamAccount::GUARD_DEVELOP, true)) {
                return Resp::success('登录成功！', '_location|' . route('py-mgr-page:develop.cp.cp'));
            }

            return Resp::error($pam->getError());
        }
        $guard = Auth::guard(PamAccount::GUARD_DEVELOP)->user();
        // todo check guard permission
        if ($guard) {
            return Resp::success('您已登录', [
                '_location' => route('py-mgr-page:develop.cp.cp'),
            ]);
        }

        return view('py-mgr-page::develop.pam.login');
    }

    public function logout()
    {
        $guard = Auth::guard(PamAccount::GUARD_DEVELOP);
        if ($guard->user()) {
            $guard->logout();
        }
        return Resp::success('退出登录成功', [
            'location' => route('py-mgr-page:develop.pam.login'),
        ]);
    }
}