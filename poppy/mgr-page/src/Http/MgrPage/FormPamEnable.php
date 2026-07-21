<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Classes\Resp;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Action\Pam;
use Poppy\System\Models\PamAccount;
use Route;

class FormPamEnable extends FormWidget
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
        $Pam    = (new Pam())->setPam(request()->user());
        $reason = input('reason', '');
        if (!$Pam->enable($this->pam->id, $reason)) {
            return Resp::error($Pam->getError());
        }

        return Resp::success('当前用户启用', '_top_reload|1');
    }

    public function data(): array
    {
        return [
            'id'   => $this->pam->id,
            'date' => $this->pam->disable_end_at,
        ];
    }

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->datetime('date', '解禁日期')->disable();
        $this->textarea('reason', '解禁原因');
    }
}
