<?php

declare(strict_types = 1);

namespace Poppy\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Poppy\Core\Classes\PyCoreDef;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Events\PermissionInitEvent;
use Poppy\Core\Rbac\Permission\Permission;
use Poppy\Core\Rbac\Permission\PermissionManager;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\System\Classes\Traits\DbTrait;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamPermission;
use Poppy\System\Models\PamRole;

/**
 * Permission Command
 */
class PermissionCommand extends Command
{
    use CoreTrait, DbTrait;

    protected $signature = 'py-core:permission
		{do : The permission action to handle, allow <lists,init>}
		{--permission= : The permission need to check}
		';

    protected $description = 'Permission manage list.';

    /**
     * @var PermissionManager
     */
    private PermissionManager $permission;

    /**
     * @var PamRole
     */
    private PamRole $pamRole;

    /**
     * @var PamPermission
     */
    private PamPermission $pamPermission;


    /**
     * @throws ApplicationException
     */
    public function __construct()
    {
        parent::__construct();
        $this->permission = $this->corePermission();

        $mdlRole       = config('poppy.core.rbac.role');
        $mdlPermission = config('poppy.core.rbac.permission');

        if (!class_exists($mdlRole) || !class_exists($mdlPermission)) {
            throw new ApplicationException('你需要配置 `poppy.core` 的 RBAC 配置');
        }

        $this->pamRole       = new $mdlRole();
        $this->pamPermission = new $mdlPermission();
    }

    /**
     * Command Handler.
     * @return bool
     * @throws Exception
     */
    public function handle()
    {
        $action = $this->argument('do');
        switch ($action) {
            case 'list':
                $this->lists();
                break;
            case 'init':
                $this->init();
                break;
            case 'menus':
                $this->checkMenus();
                break;
            case 'user':
                $role     = $this->ask('Which <role> you want assign to ?');
                $passport = $this->ask('Which <passport> you want to assign?');
                $this->user($role, $passport);
                break;
            case 'assign':
                $name = $this->ask('Which role you want assign permission ?');
                $type = $this->ask('Which permission list <user type> you want to get ?');
                $this->assign($name, $type);
                break;
            case 'check':
                $permission = $this->option('permission');
                $this->checkPermission($permission);
                break;
            default:
                $this->error(
                    sys_gen_mk(self::class, ' Command Not Exists!')
                );
                break;
        }

        return true;
    }

    public function lists()
    {
        $data = new Collection();
        $this->permission->permissions()->each(function (Permission $permission) use ($data) {
            $data->push([
                $permission->type(),
                $permission->key(),
                $permission->description(),
            ]);
        });
        $this->table(
            ['Type', 'Identification', 'Description'],
            $data->toArray()
        );
    }

    public function init()
    {
        sys_tag('py-core')->del(PyCoreDef::ckModule('module'));

        $this->permission->clearCachedPermissionNames();

        // get all permission
        $permissions = $this->permission->permissions();
        if (!$permissions->count()) {
            $this->info(sys_gen_mk(self::class, 'No permission need import.'));
            return;
        }

        event(new PermissionInitEvent($permissions));

        $num = $this->permission->cachedPermissionNames()->count();

        $this->info(sys_gen_mk(self::class, "Init {$num} permission Success!"));
    }

    /**
     * 将权限赋值给指定的用户组
     */
    private function assign($name, $type)
    {
        /** @var PamRole $role */
        $role = $this->pamRole::where('name', $name)->first();

        if (!$role) {
            $this->error(
                sys_gen_mk(self::class, 'Role [' . $name . '] not exists in table !')
            );

            return;
        }

        $permissions = $this->pamPermission::where('type', $type)->get();
        if (!$permissions) {
            $this->error(sys_gen_mk(self::class, 'Permission type [' . $type . '] has no permissions !'));
            return;
        }
        $role->syncPermission($permissions);
        $this->info(sys_gen_mk(self::class, "Save [{$type}] permission to role [{$name}] !"));
    }


    /**
     * 将角色赋值给指定的用户
     */
    private function user($role, $passport)
    {
        /** @var PamRole $role */
        $role = $this->pamRole::where('name', $role)->first();

        if (!$role) {
            $this->error(sys_gen_mk(self::class, 'Role [' . $role . '] not exists in table !'));
            return;
        }

        $pam = PamAccount::passport($passport);
        if (!$pam) {
            $this->error(sys_gen_mk(self::class, 'No such pam account !'));
            return;
        }
        $pam->attachRole($role);
        $this->info(sys_gen_mk(self::class, "Save [{$role->id}, {$role->type}] role to account [{$passport}] !"));
    }

    /**
     * @param string $permission 需要检测的权限
     */
    private function checkPermission(string $permission)
    {
        if ($this->pamPermission::where('name', $permission)->exists()) {
            $this->info(
                sys_gen_mk(self::class, 'Permission `' . $permission . '` in table ')
            );
        }
        else {
            $this->error(
                sys_gen_mk(self::class, 'Permission `' . $permission . '` not in table')
            );
        }
    }

    /**
     * 检查菜单
     */
    private function checkMenus()
    {
        // clear cache
        sys_tag('py-core')->clear();

        // calc
        $navigations = $this->coreModule()->menus();
        $format      = function ($item, $slug) {
            return [
                'title'      => $item['title'],
                'slug'       => $slug,
                'permission' => $item['permission'],
            ];
        };

        $faults = collect();
        $navigations->each(function ($item, $slug) use ($faults, $format) {

            collect($item['groups'])->each(function ($group) use ($faults, $format, $slug) {

                // 分组
                $children = collect((array) $group['children']);
                $children->map(function ($item) use ($faults, $format, $slug) {

                    $permission = $item['permission'] ?? '';
                    if ($permission && !$this->corePermission()->has($permission)) {
                        $faults->push($format($item, $slug));
                    }

                    $children = collect((array) ($item['children'] ?? []));
                    // 路由
                    $children->each(function ($item) use ($faults, $format, $slug) {
                        $permission = $item['permission'] ?? '';
                        if ($permission && !$this->corePermission()->has($permission)) {
                            $faults->push($format($item, $slug));
                        }
                    });
                });
            });

        });

        if (!$faults->count()) {
            $this->info(
                sys_gen_mk(self::class, 'All Permission are right.')
            );
        }
        else {
            $this->warn(
                sys_gen_mk(self::class, 'Error Permission in menus:')
            );
            $this->table(
                ['Title', 'Parent', 'Permission'],
                $faults->toArray()
            );
        }
    }
}
