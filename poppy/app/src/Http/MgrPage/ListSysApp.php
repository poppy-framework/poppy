<?php

declare(strict_types = 1);

namespace Poppy\App\Http\MgrPage;

use Closure;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;

class ListSysApp extends ListBase
{

    public $title = '应用管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "应用ID")->sortable()->width(100);
        $this->column('title', "标题");
        $this->column('account_type', "类型")->display(function ($type) {
            if ($type) {
                return PamAccount::kvType($type);
            }
            return '';
        });
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysApp $item */
            $item = $actions->row;
            $actions->edit(route('py-app:backend.app.establish', [$item->id]));
            if ($item->is_enable) {
                $actions->disable(route('py-app:backend.app.status', [$item->id, SysConfig::NO]), $item->title);
            }
            else {
                $actions->enable(route('py-app:backend.app.status', [$item->id, SysConfig::YES]), $item->title);
            }
        },])->fixed()->width(160);
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->equal('id', '应用 ID');
            });
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->like('title', '标题');
            });
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->equal('account_type', '账号类型')->select(PamAccount::kvType());
            });
            $filter->column(1 / 12, function (Filter $ft) {
                $ft->equal('account_id', '账号 ID');
            });
        };
    }


    public function quickButtons(): Closure
    {
        return function (Operations $operations) {
            $operations->create(route_url('py-app:backend.app.establish', '新建应用'));
        };
    }
}
