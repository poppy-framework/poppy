<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http\MgrPage;

use Illuminate\Support\Arr;
use Poppy\Ad\Action\Ad;
use Poppy\Ad\Http\Validation\AdContentRequest;
use Poppy\Ad\Models\SysAdContent;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Models\PamAccount;
use Route;
use Throwable;

class FormContentEstablish extends FormWidget
{

    public $ajax = true;

    private int $id = 0;


    private SysAdPlace $place;

    /**
     * @var PamAccount
     */
    private $item;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $id      = (int) Route::input('id');
        $placeId = (int) input('place_id');
        if ($id) {
            $this->id   = $id;
            $this->item = SysAdContent::findOrFail($this->id);
            $placeId    = $this->item->place_id;
        }
        if ($placeId) {
            $this->place = SysAdPlace::findOrFail($placeId);
        }
    }

    /**
     * @throws Throwable
     */
    public function handle($request)
    {
        $Ad = new Ad();
        /** @var AdContentRequest $req */
        $req = app(AdContentRequest::class, [$request]);
        if ($Ad->establish($req->validated(), $this->id)) {
            return Resp::success('操作成功', [
                '_top_reload' => 1,
            ]);
        }
        return Resp::error($Ad->getError());
    }

    public function data(): array
    {
        $default = [];
        if ($this->item) {
            $default       = Arr::only($this->item->toArray(), [
                'title', 'src', 'action', 'value', 'list_order', 'is_enable', 'introduce',
            ]);
            $default['at'] = implode(' - ', [$this->item->start_at, $this->item->end_at]);
        }
        return array_merge($default, [
            'place_id' => $this->place->id,
        ]);
    }

    public function form(): void
    {
        $placeTitle = '名称：' . $this->place->title . ' [ 宽度：' . $this->place->width . 'px , 高度：' . $this->place->height . 'px ]';
        $this->divider($placeTitle);
        $this->hidden('place_id');
        $this->text('title', '标题')->rules([
            Rule::nullable(),
            Rule::required(),
            Rule::string(),
            Rule::max(120),
        ]);
        $this->image('src', '图片')->rules([
            Rule::url(),
        ]);
        $this->radio('action', '操作')->options(SysAdContent::kvAction());
        $this->text('value', '操作值')->help('根据操作内容来填充的值, 链接或者路由地址');
        $this->text('list_order', '排序');
        $this->switch('is_enable', '是否启用');
        $this->dateTimeRange('at', '显示时段');
        $this->textarea('introduce', '介绍')->rules([
            Rule::max(120),
        ]);
    }
}
