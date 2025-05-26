<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Closure;
use Demo\Classes\DemoDef;
use Demo\Models\DemoGrid;
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

class ListGridOperation extends ListBase
{

    protected bool $showRowSelector = true;

    protected bool $showExporter = true;

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns(): void
    {
        $this->column('id', 'ID')->width(80);
        $this->column('title', '标题(可复制)')->width(150)->copyable();
        $this->column('image', '单个图片')->width(55)->image();
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var DemoGrid $item */
            $item = $actions->row;
            if ($item->id % 3 === 0) {
                $actions->iframe('修改密码(Primary)', DemoDef::IFRAME_INBOX_NONE)->primary();
                $actions->iframe('修改密码(Warm)', DemoDef::IFRAME_INBOX_NONE)->warm();
            }
            if ($item->id % 4 === 0) {
                $actions->iframe('弹窗打开', DemoDef::IFRAME_INBOX_NONE)->width(428);
                $actions->iframe('弹窗打开(宽度 normal)', DemoDef::IFRAME_INBOX_NONE)->widthNormal();
                $actions->iframe('弹窗打开(宽度 large)', DemoDef::IFRAME_INBOX_NONE)->widthLarge();
                $actions->request('请求并刷新', DemoDef::REQ_SUCCESS_RELOAD);
                $actions->page('跳转', DemoDef::IFRAME_INBOX)->primary();
            }
            if ($item->id % 5 === 0) {
                $actions->dropdown('下拉框 Danger', function (Operations $operations) {
                    $operations->request('请求1', DemoDef::REQ_SUCCESS_RELOAD);
                    $operations->request('弹窗 1', DemoDef::IFRAME_INBOX_NONE);
                })->color('danger');
                $actions->dropdown('下拉框 Info', function (Operations $operations) {
                    $operations->request('请求1', DemoDef::REQ_SUCCESS_RELOAD);
                    $operations->request('弹窗 1', DemoDef::IFRAME_INBOX_NONE);
                })->color('info');
            }
            if ($item->id % 7 === 0) {
                $actions->request('Icon', DemoDef::REQ_SUCCESS_RELOAD)->icon('radiation');
                $actions->request('Bi Info', DemoDef::REQ_SUCCESS_RELOAD)->icon('info');
                $actions->request('Bi Smile', DemoDef::REQ_SUCCESS_RELOAD)->icon('emoji-smile');
            }
            if ($item->id % 4 === 0) {
                $actions->request('代码提示', DemoDef::REQ_SUCCESS_RELOAD)->tooltip('代码提示');
            }
        },
        ])->fixed();
    }


    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $filter->enableExport();
        };
    }

    public function batchAction(): Closure
    {
        return function (Operations $operations) {
            $operations->batchDelete(route_url('py-sensitive-word:backend.word.delete'));
            $operations->batchIframe('测试', route('demo:web.grid.iframe'))->widthLarge()->sm();
        };
    }
}
