<?php

declare(strict_types = 1);

namespace Poppy\Sms\Http\MgrPage;

use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

class FormSettingSms extends FormSettingBase
{
    protected $group = 'py-sms::sms';

    protected $withContent = true;

    /**
     */
    public function form()
    {
        $sendTypes = sys_hook('poppy.sms.send_type');
        foreach ($sendTypes as $key => $desc) {
            $this->number('send_rate_' . $key, $desc['title'])
                ->rules([
                    Rule::min(0),
                    Rule::max(100),
                ])
                ->default(0)
                ->help('设置分流比例，未设置默认为 local 100%')
                ->width(1);
        }

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
