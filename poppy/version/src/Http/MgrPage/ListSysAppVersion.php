<?php

declare(strict_types = 1);

namespace Poppy\Version\Http\MgrPage;

use Closure;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\MgrPage\Classes\Grid\Column;
use Poppy\MgrPage\Classes\Grid\Displayer\Actions;
use Poppy\MgrPage\Classes\Grid\Filter;
use Poppy\MgrPage\Classes\Grid\Filter\Scope;
use Poppy\MgrPage\Classes\Grid\ListBase;
use Poppy\MgrPage\Classes\Operations;
use Poppy\Version\Models\SysAppVersion;

class ListSysAppVersion extends ListBase
{

    public $title = '版本管理';

    /**
     * @inheritDoc
     * @throws ApplicationException
     */
    public function columns()
    {
        $this->column('id', "ID")->sortable()->width(80);
        $this->column('title', "版本号");
        $this->column('description', "版本描述");
        $this->column('download_url', "下载地址")->downloadable();
        $this->column('created_at', "创建时间");
        $this->addColumn(Column::NAME_ACTION, '操作')->displayUsing(Actions::class, [function (Actions $actions) {
            /** @var SysAppVersion $item */
            $item = $actions->row;
            $actions->edit(route('py-version:backend.version.establish', [$item->id]));
            $actions->delete(route('py-version:backend.version.delete', [$item->id .'1']), "版本:{$item->title}");
        },])->fixed()->width(140);
    }

    /**
     * @inheritDoc
     * @return Closure
     */
    public function filter(): Closure
    {
        return function (Filter $filter) {
            $platforms = SysAppVersion::kvType();
            foreach ($platforms as $t => $v) {
                $filter->scope($t, $v)->where('platform', $t);
            }
        };
    }

    public function quickButtons(): Closure
    {
        return function (Operations $operations) {
            $platform = input(Scope::QUERY_NAME, SysAppVersion::PLATFORM_ANDROID);
            $desc     = SysAppVersion::kvType($platform);
            $operations->create(route_url('py-version:backend.version.establish', null, ['platform' => $platform]), '新增' . $desc . '版本');
            $operations->setting(route_url('py-version:backend.version.setting'));
            $operations->download(SysAppVersion::platformUrl($platform), '最新包地址', '最新包地址, 这里仅仅放置地址, 可能会出现地址无法访问的情况');
        };
    }
}
