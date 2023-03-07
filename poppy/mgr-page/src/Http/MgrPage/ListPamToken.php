<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\System\Models\PamToken;

class ListPamToken extends ListBase
{

    public $title = '登录用户管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('account_id', "用户ID");
        $this->column('device_type', "设备类型");
        $this->column('device_id', "设备ID");
        $this->column('login_ip', "登录IP");
        $this->column('expired_at', "过期时间");
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var PamToken $item */
            $item = $actions->row;
            $actions->request('下线用户', route('py-mgr-page:backend.pam.delete_token', [$item->id]))->icon('phone-flip')
                ->confirm('使用户下线, 用户可重新登录')->primary();
            $actions->request('禁用设备', route('py-mgr-page:backend.pam.ban', [$item->id, 'device']))->icon('phone')
                ->confirm('禁用此设备, 此设备无法再继续访问, 如需开启在黑名单中移除即可')->primary();
            $actions->request('禁用IP', route('py-mgr-page:backend.pam.ban', [$item->id, 'ip']))->icon('reception-4')
                ->confirm('禁用此IP, 此IP无法再继续访问, 如需开启在黑名单中移除即可')->primary();
        },])->fixed()->width(260);
    }
}
