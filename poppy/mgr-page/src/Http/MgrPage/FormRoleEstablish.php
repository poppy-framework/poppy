<?php

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Action\Role;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamRole;
use Route;

class FormRoleEstablish extends FormWidget
{

    public $ajax = true;


    private int $id;

    /**
     * @var PamRole
     */
    private PamRole $item;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $id       = (int) Route::input('id');
        $this->id = $id;
        if ($id) {
            $this->item = PamRole::findOrFail($id);
        }
    }


    public function handle()
    {
        $Role = (new Role());
        $Role->setPam(request()->user());
        if ($Role->establish(request()->all(), $this->id)) {
            return Resp::success('操作成功', '_top_reload|1;id|' . $Role->getRole()->id);
        }
        return Resp::error($Role->getError());
    }

    public function data(): array
    {
        if ($this->id) {
            return [
                'title' => $this->item->title,
                'name'  => $this->item->name,
                'type'  => $this->item->type,
            ];
        }
        return [];
    }

    public function form(): void
    {
        if ($this->id) {
            $this->select('type', '角色组')->options(PamAccount::kvType())->attribute([
                'lay-ignore',
            ])->disable();
        }
        else {
            $this->select('type', '角色组')->options(PamAccount::kvType())->rules([
                Rule::required(),
            ])->attribute([
                'lay-ignore',
            ]);
        }
        $this->text('name', '标识')->help('角色标识在后台不进行显示, 如果需要进行项目内部约定');
        $this->text('title', '角色名称')->rules([
            Rule::required(),
        ])->help('显示的名称');
    }
}
