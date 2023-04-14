<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\MgrPage\FormPamDisable;
use Poppy\MgrPage\Http\MgrPage\FormPamEnable;
use Poppy\MgrPage\Http\MgrPage\FormPamEstablish;
use Poppy\MgrPage\Http\MgrPage\FormPamMobile;
use Poppy\MgrPage\Http\MgrPage\FormPamNote;
use Poppy\MgrPage\Http\MgrPage\FormPamPassword;
use Poppy\MgrPage\Http\MgrPage\FormPamSetting;
use Poppy\MgrPage\Http\MgrPage\FormSettingLog;
use Poppy\MgrPage\Http\MgrPage\ListPamAccount;
use Poppy\MgrPage\Http\MgrPage\ListPamLog;
use Poppy\MgrPage\Http\MgrPage\ListPamToken;
use Poppy\System\Action\Ban;
use Poppy\System\Action\Pam;
use Poppy\System\Action\Sso;
use Poppy\System\Events\PamTokenBanEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamLog;
use Poppy\System\Models\PamToken;
use Throwable;

/**
 * 账户管理
 */
class PamController extends BackendController
{
    public function __construct()
    {
        parent::__construct();

        self::$permission = [
            'global'   => 'backend:py-system.pam.manage',
            'password' => 'backend:py-system.pam.password',
            'log'      => 'backend:py-system.pam.log',
            'disable'  => 'backend:py-system.pam.disable',
            'enable'   => 'backend:py-system.pam.enable',
        ];
    }

    /**
     * Display a listing of the resource.
     * @throws ApplicationException|Throwable
     */
    public function index()
    {
        return (new Grid(new PamAccount()))
            ->setLists(ListPamAccount::class)->render();
    }

    /**
     * 创建/编辑
     */
    public function establish()
    {
        return (new FormPamEstablish())->render();
    }

    /**
     * 设置密码
     */
    public function password()
    {
        return (new FormPamPassword())->render();
    }

    /**
     * 设置备注
     */
    public function note()
    {
        return (new FormPamNote())->render();
    }

    /**
     * 禁用用户
     */
    public function disable()
    {
        return (new FormPamDisable())->render();
    }

    /**
     * 启用用户
     */
    public function enable()
    {
        return (new FormPamEnable())->render();
    }

    public function setting()
    {
        return (new FormPamSetting())->render();
    }


    public function mobile()
    {
        return (new FormPamMobile())->render();
    }

    public function clearMobile(int $id)
    {
        $Pam = new Pam();
        $Pam->setPam($this->pam());
        if (!$Pam->clearMobile($id)) {
            return Resp::error($Pam->getError());
        }
        return Resp::success('已清除此用户手机通行证', '_top_reload|1');
    }

    /**
     * @return Response|JsonResponse|RedirectResponse|string
     * @throws ApplicationException
     * @throws Throwable
     */
    public function log()
    {
        return (new Grid(new PamLog()))
            ->setLists(ListPamLog::class)->render();
    }

    public function settingLog()
    {
        return (new FormSettingLog())->render();
    }

    /**
     * @return Response|JsonResponse|RedirectResponse|string
     * @throws ApplicationException
     * @throws Throwable
     */
    public function token()
    {
        return (new Grid(new PamToken()))
            ->setLists(ListPamToken::class)->render();
    }

    public function ban($id, $type)
    {
        $Ban = new Ban();
        if (!$Ban->type($id, $type)) {
            return Resp::error($Ban->getError());
        }
        return Resp::success('禁用成功', '_top_reload|1');
    }

    /**
     * 删除用户的指定 Token
     * @param $id
     * @return JsonResponse|RedirectResponse|Response
     * @throws Exception
     */
    public function deleteToken($id)
    {
        $item = PamToken::find($id);

        // 踢下线(当前用户不可访问)
        (new Sso())->banToken($item);

        event(new PamTokenBanEvent($item, 'token'));
        return Resp::error('删除用户成功, 用户已无法访问(需重新登录)', '_top_reload|1');
    }
}