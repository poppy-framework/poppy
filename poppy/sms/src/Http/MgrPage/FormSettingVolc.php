<?php

declare(strict_types = 1);

namespace Poppy\Sms\Http\MgrPage;

use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

class FormSettingVolc extends FormSettingBase
{
    protected $title = '火山云短信配置';

    protected $withContent = true;

    protected $group = 'py-sms::sms';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('volc_access_key', 'AccessKey')->rules([
            Rule::nullable(),
        ]);
        $this->text('volc_access_secret', 'AccessSecret')->rules([
            Rule::nullable(),
        ]);
        $this->text('volc_default_account', '默认消息组ID')->rules([
            Rule::nullable(),
        ]);
    }
}
