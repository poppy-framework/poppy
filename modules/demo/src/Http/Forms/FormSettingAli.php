<?php namespace Demo\Http\Forms;


use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

/**
 * 支付宝-支付设置
 */
class FormSettingAli extends FormSettingBase
{
    public $title = '支付宝设置';

    protected $group = 'demo::site';

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $this->radio('alipay_app_open', 'APP-支付开关')->options([
            '0' => '关闭',
            '1' => '开启',
        ]);
        $this->textarea('alipay_private_key', 'APP-私钥')->rules([
            Rule::required(),
            Rule::nullable(),
            Rule::string(),
        ]);
    }
}