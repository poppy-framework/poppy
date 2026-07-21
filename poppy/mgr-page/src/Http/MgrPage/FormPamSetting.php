<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Widgets\FormWidget;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Classes\Traits\UserSettingTrait;
use Poppy\System\Models\PamAccount;
use Route;

class FormPamSetting extends FormWidget
{
    use UserSettingTrait;

    public $ajax = true;

    private PamAccount $pam;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $id        = Route::input('id');
        $this->pam = PamAccount::findOrFail($id);
    }

    public function handle()
    {
        $hour = round((float) input('expired_hour'), 1);
        $this->userSettingSet($this->pam->id, PySystemDef::uskAccount(), [
            'expired_hour' => $hour,
        ], [
            'expired_hour',
        ]);

        return Resp::success('已设置');
    }

    public function data(): array
    {
        $settings = $this->userSettingGet($this->pam->id, PySystemDef::uskAccount());

        return [
            'expired_hour' => $settings['expired_hour'] ?? '12',
        ];
    }

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->text('expired_hour', '登录有效期')->rules([
            Rule::numeric(),
            Rule::between(3, 24),
        ])->help('登录有效期的时间为 3- 24 小时之间, 允许存在 1 位小数, 超过的小数位数将四舍五入, 默认的有效时间为(12 小时)');
    }
}
