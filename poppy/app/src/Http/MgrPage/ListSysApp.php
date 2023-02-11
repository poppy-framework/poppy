<?php
declare(strict_types = 1);

namespace Poppy\App\Http\MgrPage;

use Closure;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Grid\Tools\BaseButton;
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
        $this->column('account_type', "用户类型")->display(function ($type) {
            if ($type) {
                return PamAccount::kvType($type);
            }
            return '';
        });
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

    /**
     * @inheritDoc
     */
    public function actions()
    {
        $this->addColumn(Column::NAME_ACTION, '操作')
            ->displayUsing(Actions::class, [
                function (Actions $actions) {
                    /** @var SysApp $item */
                    $item    = $actions->row;
                    $buttons = [
                        new BaseButton('<i class="fa fa-edit"></i>', route('py-app:backend.app.establish', [$item->id]), [
                            'title' => "编辑 [{$item->title}]",
                            'class' => 'J_iframe',
                        ]),
                    ];
                    if ($item->is_enable) {
                        $buttons[] = new BaseButton('<i class="fa fa-check-circle text-success"></i>', route('py-app:backend.app.status', [$item->id, SysConfig::NO]), [
                            'title'        => "禁用应用[{$item->title}]",
                            'data-confirm' => "确定要禁用 [{$item->title}]",
                            'class'        => 'J_request',
                        ]);
                    }
                    else {
                        $buttons[] = new BaseButton('<i class="fa fa-ban text-danger"></i>', route('py-app:backend.app.status', [$item->id, SysConfig::YES]), [
                            'title'        => "启用应用[{$item->title}]",
                            'data-confirm' => "确定要启用 [{$item->title}]",
                            'class'        => 'J_request',
                        ]);
                    }

                    $actions->append($buttons);
                },
            ]);
    }


    public function quickButtons(): array
    {
        return [
            new BaseButton('<i class="fa fa-plus"></i> 新增', route_url('py-app:backend.app.establish'), [
                'title' => "新建应用",
                'class' => 'J_iframe layui-btn layui-btn-sm',
            ]),
        ];
    }
}
