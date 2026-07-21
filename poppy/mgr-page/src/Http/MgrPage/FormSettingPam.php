<?php

declare(strict_types = 1);

namespace Poppy\MgrPage\Http\MgrPage;

use Poppy\Framework\Validation\Rule;
use Poppy\MgrPage\Classes\Form\FormSettingBase;
use Poppy\System\Action\Sso;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\SysConfig;

class FormSettingPam extends FormSettingBase
{
    protected $title = '账号安全';

    protected $group = 'py-system::pam';

    public function data(): array
    {
        $data = parent::data();

        return array_merge($data, [
            'lifetime' => sys_setting('py-system::pam.lifetime') ?: 12,
        ]);
    }

    public function form(): void
    {
        $groups = (new Sso())->groupDesc(true);
        $this->text('prefix', '账号前缀')->rules([
            Rule::required(),
        ])->placeholder('请输入账号前缀, 用于账号注册默认用户名生成');
        $this->switch('auto_enable', '账号自动解封')->help('账号自动解封, 默认时间 15 分钟执行一次');
        $this->textarea('test_account', '测试账号')->placeholder('请填写测试账号, 每行一个')->help('在此测试账号内的应用, 不需要正确的验证码即可登录');
        $this->switch('is_remember', '是否记住登录')->help('根据记住登录的时间来设定账号登录的有效期');
        $this->text('remember_hour', '记住登录')->placeholder('记住登录的时长')->rules([
            Rule::numeric(),
            Rule::between(1, 400),
        ])->help('设置记住登录的时长, 默认 60 天, 最长时间不超过 400 天(浏览器安全限制)');
        $this->text('lifetime', '默认登录时长')->rules([
            Rule::numeric(),
            Rule::between(3, 3 * 24),
        ])->help('用户多长时间无操作之后退出登录, 允许范围 3-' . (3 * 24) .
            ' 小时(3 天), 默认值: 12 小时, 如果设定了退出浏览器失效, 则此项不起作用, 当前退出设定:' .
            (config('session.expire_on_close') ? '已设定' : '未设定')
        );

        /* 单点登录
         * ---------------------------------------- */
        $this->divider('单点登录设定');
        $this->radio('sso_type', '单点登录类型')->options(Sso::kvType())->stacked()->rules([
            Rule::required(),
        ])->help('分组模式支持互踢(' . Sso::GROUP_KICKED . '), 不限(' . Sso::GROUP_UNLIMITED . '). 当前分组模式互踢规则为 :' . $groups);
        $this->radio('sso_os_empty_hold', '拦截空 OS')->options(SysConfig::kvStrYn())->rules([
            Rule::required(),
        ])->help('如果获取 x-os 为空情况下是否对用户进行拦截, 默认(否)');
        $this->text('sso_device_num', '最大设备数量')->help('启用数量限制模式时, 非无限模式下的数量限制, 默认最大数量为10')->rules([
            Rule::max(10), Rule::required(), Rule::numeric(),
        ]);

        /* 密码策略
         * ---------------------------------------- */
        $this->divider('密码策略');
        $this->checkbox('backend_pwd_strength', '后台密码策略')->options(PamAccount::kvPwdStrength());
        $this->checkbox('user_pwd_strength', '用户密码策略')->options(PamAccount::kvPwdStrength())
            ->help('密码策略: 系统默认密码长度 6-20, 可选范围是 0-9, a-z, A-Z, 特殊字符(*.[]-!@#$%^&()~]+)');

        /* 账号验证码
         * ---------------------------------------- */
        $this->divider('账号验证码');
        $this->text('captcha_expired', '验证码有效期(分钟)')->rules([
            Rule::integer(),
        ])->default(5)->help('默认有效期 5 分钟');
        $this->text('captcha_length', '验证码长度')->help('验证码长度, 默认是 6 位, 可以设置的长度值是 4-10 位')->rules([
            Rule::between(4, 10), Rule::integer(),
        ])->default(6);
    }
}
