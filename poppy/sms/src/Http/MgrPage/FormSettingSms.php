<?php

declare(strict_types = 1);

namespace Poppy\Sms\Http\MgrPage;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Poppy\Core\Classes\Contracts\SettingContract;
use Poppy\Framework\Classes\Resp;
use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\Field\Number;
use Poppy\MgrPage\Classes\Form\FormSettingBase;
use Poppy\System\Setting\Repository\SettingRepository;

class FormSettingSms extends FormSettingBase
{
    protected $group = 'py-sms::sms';

    protected $withContent = true;

    public function handle(Request $request)
    {
        $items = $request->all();
        /** @var SettingRepository $Setting */
        $Setting = app(SettingContract::class);
        foreach ($items as $key => $item) {
            if (Str::startsWith($key, 'send_rate_')) {
                $value = (int) $item;
                if ($value < 0 || $value > 100) {
                    return Resp::error('错误的分流比例');
                }
                $Setting->set($this->group . '.' . $key, $value);
            }
        }

        return parent::handle($request);
    }

    public function form(): void
    {
        $sendTypes = sys_hook('poppy.sms.send_type');
        $table     = [];
        foreach ($sendTypes as $key => $desc) {
            $table[] = [
                (new Number('send_rate_' . $key, [$desc['title']]))->default((int) sys_setting($this->group . '.send_rate_' . $key)),
            ];
        }

        $this->tableInput('rate', '分流比例')->table($table)->help('设置分流比例, 未设置默认为 local');
        $this->text('sign', '默认签名')->rules([Rule::nullable()]);

        foreach ($sendTypes as $desc) {
            if (isset($desc['setting'])) {
                $url  = route($desc['route']);
                $link = <<<Link
<a class="J_iframe" href="$url" data-height="600"><i class="bi bi-sliders"></i> {$desc['title']}设置</a>
Link;
                $this->html($link, $desc['title']);
            }
        }
    }
}
