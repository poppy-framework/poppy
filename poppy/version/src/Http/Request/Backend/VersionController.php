<?php

declare(strict_types = 1);

namespace Poppy\Version\Http\Request\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\Request\Backend\BackendController;
use Poppy\Version\Action\Version;
use Poppy\Version\Classes\PyVersionDef;
use Poppy\Version\Http\MgrPage\FormSettingVersion;
use Poppy\Version\Http\MgrPage\FormVersionEstablish;
use Poppy\Version\Http\MgrPage\ListSysAppVersion;
use Poppy\Version\Models\SysAppVersion;
use Throwable;

/**
 * 版本管理控制器
 */
class VersionController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        self::$permission = [
            'global' => 'backend:py-version.main.manage',
        ];
    }

    /**
     * @throws Throwable
     * @throws ApplicationException
     */
    public function index()
    {
        $grid = new Grid(new SysAppVersion());
        $grid->setLists(ListSysAppVersion::class);

        return $grid->render();
    }

    /**
     * 创建/编辑
     *
     * @param null $id
     *
     * @throws ApplicationException
     */
    public function establish($id = null)
    {
        $form = new FormVersionEstablish();
        $form->setPlatform(input('platform'));
        $form->setId($id);

        return $form->render();
    }

    /**
     * 设置
     */
    public function setting()
    {
        return (new FormSettingVersion())->render();
    }

    public function clearCache()
    {
        sys_tag('py-version')->del([
            PyVersionDef::ckMaxVersion(),
            PyVersionDef::ckVersions(),
        ]);

        return Resp::success('已清理');
    }

    /**
     * 删除
     *
     * @return Response|JsonResponse|RedirectResponse
     */
    public function delete($id)
    {
        $Version = new Version();
        if (!$Version->delete((int) $id)) {
            return Resp::error('删除失败');
        }

        return Resp::success('删除成功', '_top_reload|1');
    }
}
