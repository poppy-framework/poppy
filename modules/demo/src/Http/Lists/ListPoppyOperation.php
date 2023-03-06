<?php

declare(strict_types = 1);

namespace Demo\Http\Lists;

use Demo\Classes\DemoDef;
use Demo\Models\DemoWebapp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;

class ListPoppyOperation extends ListBase
{
    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', 'ID')->width(80);
        $this->column('title', '标题(可复制)')->width(150)->copyable();
        $this->column('image', '单个图片')->width(55)->image();
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var DemoWebapp $item */
            $item = $actions->row;
            if ($item->id % 3 === 0) {
                $actions->iframe('修改密码(Primary)', DemoDef::IFRAME_INBOX_NONE)->primary();
                $actions->iframe('修改密码(Warm)', DemoDef::IFRAME_INBOX_NONE)->warm();
            }
            if ($item->id % 4 === 0) {
                $actions->iframe('弹窗打开', DemoDef::IFRAME_INBOX_NONE)->width(428);
                $actions->iframe('弹窗打开(宽度 normal)', DemoDef::IFRAME_INBOX_NONE)->normal();
                $actions->iframe('弹窗打开(宽度 large)', DemoDef::IFRAME_INBOX_NONE)->large();
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
                $actions->request('Bi Info', DemoDef::REQ_SUCCESS_RELOAD)->icon('bi:info');
                $actions->request('Bi Smile', DemoDef::REQ_SUCCESS_RELOAD)->icon('bi:emoji-smile');
            }
            if ($item->id % 4 === 0) {
                $actions->request('代码提示', DemoDef::REQ_SUCCESS_RELOAD)->tooltip('代码提示');
            }
        },
        ])->fixed();
    }
}
