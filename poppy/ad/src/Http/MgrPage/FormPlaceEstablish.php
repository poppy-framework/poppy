<?php

declare(strict_types = 1);

namespace Poppy\Ad\Http\MgrPage;

use Illuminate\Support\Arr;
use Poppy\Ad\Action\Place;
use Poppy\Ad\Http\Validation\AdPlaceRequest;
use Poppy\Ad\Models\SysAdPlace;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Models\PamAccount;
use Route;
use Throwable;

class FormPlaceEstablish extends FormWidget
{
    public $ajax = true;

    private int $id = 0;

    /**
     * @var PamAccount
     */
    private $item;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $id = (int) Route::input('id');
        if ($id) {
            $this->id   = $id;
            $this->item = SysAdPlace::findOrFail($this->id);
        }
    }

    /**
     * @throws Throwable
     */
    public function handle($request)
    {
        $Place = new Place();
        /** @var AdPlaceRequest $req */
        $req = app(AdPlaceRequest::class, [$request]);
        if ($Place->establish($req->validated(), $this->id)) {
            return Resp::success('操作成功', [
                '_top_reload' => 1,
            ]);
        }

        return Resp::error($Place->getError());
    }

    public function data(): array
    {
        return $this->item
            ? Arr::only($this->item->toArray(), [
                'title', 'height', 'width', 'thumb', 'introduce',
            ])
            : [];
    }

    public function form(): void
    {
        $this->text('title', '标题')->rules([
            Rule::nullable(),
            Rule::required(),
            Rule::string(),
            Rule::max(120),
        ]);
        $this->text('width', '建议宽度')->rules([
            Rule::numeric(),
        ]);
        $this->text('height', '建议高度')->rules([
            Rule::numeric(),
        ]);
        $this->image('thumb', '示意图')->rules([
            Rule::url(),
        ]);
        $this->textarea('introduce', '介绍')->rules([
            Rule::max(120),
        ]);
    }
}
