<?php namespace Demo\Http\Forms;


use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;

/**
 * 支付宝-支付设置
 */
class FormDemoAli extends FormSettingBase
{
    public $title = '支付宝设置';

    protected $group = 'demo::site';

    /**
     * Build a form here.
     */
    public function form(): void
    {
        $link = <<<EO
<a>请填写回调地址后再进行授权</a>
EO;
        $this->html($link, 'Vivo 平台授权');
        $this->textarea('alipay_private_key', 'APP-私钥')->rules([
            Rule::required(),
            Rule::nullable(),
            Rule::string(),
        ]);
    }
}