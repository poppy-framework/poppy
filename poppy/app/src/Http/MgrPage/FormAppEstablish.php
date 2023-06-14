<?php

declare(strict_types = 1);

namespace Poppy\App\Http\MgrPage;

use Illuminate\Http\Request;
use Poppy\App\Action\App;
use Poppy\App\Http\Validation\AppEstablishRequest;
use Poppy\App\Models\SysApp;
use Poppy\Core\Classes\Traits\CoreTrait;
use Poppy\Core\Rbac\Permission\Permission;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Models\PamAccount;
use Route;

class FormAppEstablish extends FormWidget
{

    use CoreTrait;

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
        $validated = app(AppEstablishRequest::class, [$request])->validated();
        if ($this->app->establish($validated, $this->id)) {
            return Resp::success('添加成功', [
                '_top_reload' => 1,
                'id'          => $this->app->getItem()->id,
            ]);
        }
        return Resp::error($this->app->getError());
    }

    public function data(): array
    {
        return $this->item ? $this->item->toArray() : [];
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
        $this->text('account_id', '绑定用户ID')->rules([
            Rule::numeric(),
        ])->help('用户的应用, 需要绑定到用户ID 上, 并且和用户类型相匹配');

        $permissions = [];
        $this->corePermission()->permissions()->each(function (Permission $permission) use (&$permissions) {
            if ($permission->type() === 'app') {
                $permissions[$permission->key()] = $permission->description();
            }
        });

        $this->checkbox('permissions', '应用权限')->options($permissions);
        $this->textarea('note', '应用备注');
    }
}
