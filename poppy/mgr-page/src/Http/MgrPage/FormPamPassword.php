<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Illuminate\Support\Facades\Validator;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Action\Pam;
use Poppy\System\Models\PamAccount;
use Route;

class FormPamPassword extends FormWidget
{
    public $ajax = true;

    /**
     * @var PamAccount
     */
    private $pam;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $id        = (int) Route::input('id');
        $this->pam = PamAccount::findOrFail($id);
    }

    public function handle()
    {
        $validator = Validator::make(input(), [
            'password' => [
                Rule::required(),
                Rule::confirmed(),
            ],
        ]);
        if ($validator->fails()) {
            return Resp::error($validator->errors());
        }

        $password = input('password');

        $Pam = new Pam();
        if (sys_is_demo()) {
            return Resp::error('演示模式下无法修改密码');
        }
        if ($Pam->setPassword($this->pam, $password)) {
            return Resp::success('设置密码成功', '_top_reload|1');
        }

        return Resp::error($Pam->getError());
    }

    public function data(): array
    {
        return [
            'id'       => $this->pam->id,
            'username' => $this->pam->username,
        ];
    }

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->text('username', '用户名')->disable();
        $this->password('password', '密码');
        $this->password('password_confirmation', '重复密码');
    }
}
