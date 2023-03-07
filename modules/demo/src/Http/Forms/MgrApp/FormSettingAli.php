<?php

namespace Demo\Http\Forms\MgrApp;

use Poppy\Framework\Validation\Rule;
use Poppy\MgrApp\Classes\Form\SettingBase;

/**
 * 支付宝-支付设置
 */
class FormSettingAli extends SettingBase
{
    protected string $title = '支付宝设置';

    protected string $group = 'demo::site';

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->radio('alipay_app_open', 'APP-支付开关')->options(['0' => '关闭', '1' => '开启']);
        $this->textarea('alipay_private_key', 'APP-私钥')->rules([Rule::required(), Rule::nullable(), Rule::string()]);
    }
}