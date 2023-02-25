<?php

namespace Poppy\MgrPage\Http\MgrPage;

use Auth;
use Closure;
use Illuminate\Support\Str;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Poppy\System\Models\PamRoleAccount;

class ListPamAccount extends ListBase
{

    public $title = '账号管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $user = Auth::user();
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('username', "用户名")->width(130);
        $this->column('mobile', "手机号")->width(140);
        $this->column('email', "邮箱");
        $this->column('login_times', "登录次数")->widthAsId();
        $this->column('created_at', "注册/创建时间")->widthAsDatetime();
        $this->column('reg_ip', "注册 IP")->widthAsIp();
        $this->column('logined_at', "最后登录时间")->widthAsDatetime();
        $this->column('note', "备注")->widthAsNote();
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) use ($user) {
            /** @var PamAccount $item */
            $item = $actions->row;

            $actions->dropdown('编辑', function (Operations $operations) use ($item) {
                $operations->iframe('修改密码', route('py-mgr-page:backend.pam.password', [$item->id]))->icon('lay:password')->primary();
                $operations->iframe('编辑', route_url('py-mgr-page:backend.pam.establish', [$item->id]))->icon('lay:edit')->primary();
                $operations->iframe('备注', route_url('py-mgr-page:backend.pam.note', [$item->id]))->icon('lay:survey')->primary();
            })->icon('lay:edit');
            if ($user->can('disable', $item)) {
                $actions->iframe('已启用', route_url('py-mgr-page:backend.pam.disable', [$item->id]))->icon('lay:ok-circle')->default()
                    ->tooltip('当前启用, 点击禁用');
            }
            if ($user->can('enable', $item)) {
                $actions->iframe('已禁用', route_url('py-mgr-page:backend.pam.enable', [$item->id]))->icon('lay:time')->danger()
                    ->tooltip('当前禁用, 点击启用');
            }
        },])->fixed()->width(165);
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $type  = input(Scope::QUERY_NAME, PamAccount::TYPE_BACKEND);
            $roles = PamRole::getLinear($type);
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->where(function ($query) {
                    $passport = input('passport');
                    $type     = PamAccount::passportType($passport);
                    if ($type === PamAccount::REG_TYPE_MOBILE && !Str::contains($passport, '-')) {
                        // 默认拼接国内手机号
                        $passport = '86-' . $passport;
                    }
                    $query->where($type, $passport);
                }, '手机/用户名/邮箱', 'passport');
            });
            $filter->column(1 / 12, function (Filter $column) use ($roles) {
                $column->where(function ($query) {
                    $roleId      = data_get($this, 'input');
                    $account_ids = PamRoleAccount::where('role_id', $roleId)->pluck('account_id');
                    $query->whereIn('id', $account_ids);
                }, '用户角色', 'role_id')->select($roles);
            });
            $types = PamAccount::kvType();
            foreach ($types as $t => $v) {
                $filter->scope($t, $v)->where('type', $t);
            }
        };
    }

    public function quickButtons(): Closure
    {
        $scope = input(Scope::QUERY_NAME);
        return function (Operations $operations) use ($scope) {
            $operations->page('封禁管理', route_url('py-mgr-page:backend.ban.index', null, [Scope::QUERY_NAME => $scope]))
                ->icon('lay:time')->sm();
            $operations->page('登录凭证', route('py-mgr-page:backend.pam.token'))->icon('lay:chart')
                ->tooltip('登录用户管理, 开启单点登录可用')->sm();
            $operations->create(route_url('py-mgr-page:backend.pam.establish', null, ['type' => $scope]), '新增账号');
        };
    }
}
