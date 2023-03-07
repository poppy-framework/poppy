<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamBan;
use Poppy\System\Models\SysConfig;

class ListPamBan extends ListBase
{

    public $title = '风险拦截';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('type', "类型")->display(function ($type) {
            return PamBan::kvType($type);
        });
        $this->column('value', "限制值");
        $this->column('note', "备注");
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var PamBan $item */
            $item = $actions->row;
            $actions->delete(route('py-mgr-page:backend.ban.delete', [$item->id]), $item->type . $item->value);
        },])->fixed()->width(70);
    }


    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $types = PamAccount::kvType();
            foreach ($types as $t => $v) {
                $filter->scope($t, $v)->where('account_type', $t);
            }
        };
    }


    public function quickButtons(): Closure
    {
        $type = input(Scope::QUERY_NAME, PamAccount::TYPE_USER);
        return function (Operations $operations) use ($type) {
            $status = sys_setting('py-system::ban.status-' . $type, SysConfig::STR_NO);
            $url    = route_url('py-mgr-page:backend.ban.status', null, ['type' => $type,]);
            if ($status === 'Y') {
                $operations->disable($url, '风险拦截');
            }
            else {
                $operations->enable($url, '风险拦截');
            }

            $isBlack = sys_setting('py-system::ban.type-' . $type, PamBan::WB_TYPE_BLACK) === PamBan::WB_TYPE_BLACK;
            $url     = route_url('py-mgr-page:backend.ban.type', null, ['type' => $type,]);
            if ($isBlack) {
                $operations->request('黑名单模式', $url)->icon('pause-circle')->tooltip('当前黑名单, 点击切换到白名单')->sm()
                    ->confirm('当前黑名单, 是否切换到白名单?')->danger();
            }
            else {
                $operations->request('白名单模式', $url)->icon('play-circle')->tooltip('当前白名单, 点击切换到黑名单')->sm()
                    ->confirm('当前白名单, 是否切换到黑名单?');
            }
            $operations->create(route_url('py-mgr-page:backend.ban.establish', null, ['type' => $type,]), '新增');
            $operations->setting(route_url('py-mgr-page:backend.ban.setting', null, ['type' => $type,]));
        };
    }
}
