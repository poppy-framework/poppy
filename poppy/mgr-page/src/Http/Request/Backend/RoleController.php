<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\Request\Backend;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid;
use Poppy\MgrPage\Http\MgrPage\FormRoleEstablish;
use Poppy\MgrPage\Http\MgrPage\ListPamRole;
use Poppy\System\Action\Role;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Throwable;
use View;

/**
 * 角色管理控制器
 */
class RoleController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $types = PamAccount::kvType();
        View::share(compact('types'));

        self::$permission = [
            'global' => 'backend:py-system.role.manage',
            'menu'   => 'backend:py-system.role.permissions',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @throws ApplicationException
     * @throws Throwable
     */
    public function index()
    {
        return (new Grid(new PamRole()))
            ->setLists(ListPamRole::class)->render();
    }

    /**
     * 编辑 / 创建
     *
     * @throws Throwable
     */
    public function establish()
    {
        return (new FormRoleEstablish())->render();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id 角色id
     *
     * @return JsonResponse|RedirectResponse|Response
     *
     * @throws Exception
     */
    public function delete(int $id)
    {
        $role = $this->action();
        if (!$role->delete($id)) {
            return Resp::error($role->getError());
        }

        return Resp::success('删除成功', '_top_reload|1');
    }

    /**
     * 带单列表
     *
     * @param int $id 角色id
     *
     * @return Factory|JsonResponse|RedirectResponse|Response
     */
    public function menu(int $id)
    {
        $role = PamRole::find($id);
        if (is_post()) {
            $perms = (array) input('permission_id');
            $Role  = $this->action();
            if (!$Role->savePermission($id, $perms)) {
                return Resp::success($Role->getError());
            }

            return Resp::success('保存会员权限配置成功!', '_reload|1');
        }
        $permission = (new Role())->permissions($id);

        if (!count($permission)) {
            return Resp::error('暂无权限信息(请检查是否初始化权限)！');
        }
        $groupedPermission = collect($permission)->groupBy(function ($item, $key) {
            if (Str::contains($key, 'py-')) {
                return 'poppy';
            }

            return 'module';
        });

        return view('py-mgr-page::backend.role.menu', [
            'permission' => $groupedPermission,
            'role'       => $role,
        ]);
    }

    private function action(): Role
    {
        return (new Role())->setPam($this->pam);
    }
}
