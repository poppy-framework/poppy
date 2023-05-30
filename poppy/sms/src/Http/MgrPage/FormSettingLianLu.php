<?php

declare(strict_types = 1);

namespace Poppy\Sms\Http\MgrPage;

use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

class FormSettingLianLu extends FormSettingBase
{

    protected $title = '联麓短信配置';

    protected $withContent = true;

    protected $group = 'py-sms::sms';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('lianlu_mch_id', '联麓 企业ID')->rules([
            Rule::nullable(),
        ]);
        $this->text('lianlu_app_id', '联麓 AppId')->rules([
            Rule::nullable(),
        ]);
        $this->text('lianlu_app_key', '联麓 AppKey')->rules([
            Rule::nullable(),
        ]);
        $this->text('lianlu_cty_mch_id', '联麓国际 企业ID')->rules([
            Rule::nullable(),
        ]);
        $this->text('lianlu_cty_app_id', '联麓国际 AppId')->rules([
            Rule::nullable(),
        ]);
        $this->text('lianlu_cty_app_key', '联麓国际 AppKey')->rules([
            Rule::nullable(),
        ]);
    }
}
