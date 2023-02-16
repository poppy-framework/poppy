<?php

declare(strict_types = 1);

namespace Poppy\App\Http\MgrPage;

use Illuminate\Http\Request;
use Poppy\App\Action\App;
use Poppy\App\Models\SysApp;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Models\PamAccount;
use Route;

class FormAppEstablish extends FormWidget
{

    public $ajax = true;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var null|SysApp
     */
    private ?SysApp $item = null;


    /**
     * 分类
     * @var App
     */
    private App $app;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->app = new App();
        $id        = (int) Route::input('id');
        $id && $this->app->init($id);

        if ($id) {
            $this->item = $this->app->getItem();
        }
        $this->id = $id;
    }

    public function handle(Request $request)
    {
        $data = array_merge($request->all());
        if ($this->app->establish($data, $this->id)) {
            return Resp::success('添加成功', [
                '_top_reload' => 1,
                'id'          => $this->app->getItem()->id,
            ]);
        }
        return Resp::error($this->app->getError());
    }

    public function data(): array
    {
        return $this->item ? [
            'title'        => $this->item->title,
            'name'         => $this->item->name,
            'secret'       => $this->item->secret,
            'account_type' => $this->item->account_type,
            'account_id'   => $this->item->account_id,
            'note'         => $this->item->note,
        ] : [
        ];
    }

    public function form(): void
    {
        $this->text('title', '应用名称')->rules([
            Rule::required(),
        ]);
        $this->text('secret', '应用密钥')->rules([
            Rule::string(),
            Rule::size(32),
            Rule::required(),
        ]);
        $this->text('name', '应用标识')->rules([
            Rule::string(),
        ]);
        $this->select('account_type', '应用账户类型')->rules([
            Rule::nullable(),
        ])->options(PamAccount::kvType());
        $this->text('account_id', '用户ID')->rules([
            Rule::numeric(),
        ])->help('用户的应用, 需要绑定到用户ID 上, 并且和用户类型相匹配');
        $this->textarea('note', '应用备注');
    }
}
