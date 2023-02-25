<?php

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Action\Pam;
use Poppy\System\Models\PamAccount;
use Route;

class FormPamDisable extends FormWidget
{
    public $ajax = true;

    /**
     * @var PamAccount
     */
    private $pam;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $id        = Route::input('id');
        $this->pam = PamAccount::findOrFail($id);
    }

    public function handle()
    {
        if (!$this->pam) {
            return Resp::error('您尚未选择用户!');
        }

        $date   = input('datetime', '');
        $reason = input('reason', '');
        $Pam    = (new Pam())->setPam(request()->user());
        if (!$Pam->disable($this->pam->id, $date, $reason)) {
            return Resp::error($Pam->getError());
        }

        return Resp::success('当前用户已封禁', '_top_reload|1');

    }

    public function data(): array
    {
        return [
            'id'       => $this->pam->id,
            'datetime' => $this->pam->disable_end_at,
            'reason'   => $this->pam->disable_reason,
        ];
    }

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->datetime('datetime', '解禁时间')->rules([
            Rule::required(),
        ])->placeholder('选择解禁时间');
        $this->textarea('reason', '封禁原因');
    }
}
