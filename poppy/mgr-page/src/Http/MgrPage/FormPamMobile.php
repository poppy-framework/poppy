<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Auth;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Exceptions\ApplicationException;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Action\Pam;
use Poppy\System\Models\PamAccount;
use Route;

class FormPamMobile extends FormWidget
{
    public $ajax = true;

    /**
     * @var PamAccount
     */
    private $pam;


    /**
     * @throws ApplicationException
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $id        = Route::input('id');
        $this->pam = PamAccount::findOrFail($id);
        /** @var PamAccount $user */
        $user = Auth::user();
        if (!$user->can('mobile', $this->pam)) {
            throw new ApplicationException('你无权修改通行证');
        }
        
    }

    public function handle()
    {
        $mobile = input('mobile');
        $Pam    = new Pam();
        if (!$Pam->setMobile($this->pam, $mobile)) {
            return Resp::error($Pam->getError());
        }
        return Resp::success('设置成功', '_top_reload|1');
    }

    public function data(): array
    {
        return [
            'username' => $this->pam->username,
        ];
    }

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->text('username', '用户名')->readonly();
        $this->text('mobile', '手机号')->rules([
            Rule::required(),
            Rule::string(),
            Rule::size(11),
        ]);
    }
}
