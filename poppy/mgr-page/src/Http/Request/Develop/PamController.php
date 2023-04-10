<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Develop;

use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Action\Pam;
use Poppy\System\Http\Validation\PamLoginRequest;
use Poppy\System\Models\PamAccount;

/**
 * 开发平台用户登录控制器
 */
class PamController extends DevelopController
{

    /**
     * @param Request $req
     * @return Application|Factory|JsonResponse|RedirectResponse|Response|View
     * @throws ApplicationException
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function login(Request $req)
    {
        if (is_post()) {
            $pam = new Pam();
            /** @var PamLoginRequest $request */
            $request = app(PamLoginRequest::class, [$req]);
            $valid   = $request->scene('password')->validated();
            if ($pam->loginCheck($valid['passport'], $valid['password'], PamAccount::GUARD_DEVELOP)) {
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