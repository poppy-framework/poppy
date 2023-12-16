<?php

declare(strict_types = 1);

namespace Poppy\Backstage\Http\Validation;

use Poppy\Framework\Application\Request;
use Poppy\Framework\Validation\Rule;
use Poppy\System\Models\PamAccount;

/**
 * 管理登录的请求
 */
class MgrLoginRequest extends Request
{

    protected bool $isValidate = false;

    public function attributes(): array
    {
        return [
            'passport' => '通行证',
            'captcha'  => '验证码',
            'password' => '密码',
            'x-os'     => '软件平台',
            'x-id'     => '设备标识',
        ];
    }

    public function scenes(): array
    {
        return [
            'captcha'  => [
                'passport', 'captcha', 'x-os', 'x-id'
            ],
            'password' => [
                'passport', 'password', 'x-os', 'x-id'
            ],
            'passport' => [
                'passport', 'x-os', 'x-id'
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'passport' => [
                Rule::required(),
            ],
            'captcha'  => [
                Rule::required(),
                Rule::numeric(),
            ],
            'password' => [
                Rule::required(),
                Rule::string(),
                Rule::simplePwd(),
                Rule::between(6, 20),
            ],
            'x-os'     => [
                Rule::required(),
                Rule::in(PamAccount::kvPlatform()),
            ],
            'x-id'     => [
                Rule::required(),
            ],
        ];
    }

    /**
     * 获取通行证
     * @return string
     */
    public function getPassport(): string
    {
        return (string) $this->input('passport', '');
    }

    /**
     * 获取HeaderOS
     * @return string
     */
    public function getOs(): string
    {
        return $this->input('x-os', '');
    }

    /**
     * 获取 Header ID
     * @return string
     */
    public function getId(): string
    {
        return $this->input('x-id', '');
    }

    /**
     * 获取验证码
     * @return string
     */
    public function getCaptcha(): string
    {
        return $this->input('captcha', '');
    }

    /**
     * 获取密码
     * @return string
     */
    public function getPassword(): string
    {
        return $this->input('password', '');
    }
}
