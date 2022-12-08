<?php

declare(strict_types = 1);

namespace Poppy\System\Classes;


class PySystemDef
{
    /**
     * 模型注释
     * @return string
     */
    public static function ckModelComment(): string
    {
        return 'model-comment';
    }

    /**
     * 设置
     * @return string
     */
    public static function ckSetting(): string
    {
        return 'tag:py-system:setting';
    }

    /**
     * 设置
     * @return string
     */
    public static function ckPamRelParent(): string
    {
        return 'pam-rel-parent';
    }

    /**
     * 一次验证码
     * @return string
     */
    public static function ckTagVerificationOnce(): string
    {
        return 'tag:py-system:verification-once_code';
    }

    /**
     * 自定义验证
     * @return string
     */
    public static function ckTagVerificationWord(): string
    {
        return 'tag:py-system:verification-word';
    }

    /**
     * 验证码
     * @return string
     */
    public static function ckTagVerificationCaptcha(): string
    {
        return 'tag:py-system:verification-captcha';
    }

    /**
     * 单点登录的Hash(允许访问的)
     * @param string $type 类型
     * @return string
     */
    public static function ckTagSso(string $type): string
    {
        return 'tag:py-system:sso-' . $type;
    }

    /**
     * 用户单一设备禁用
     * @param string $type 账号类型
     * @return string
     */
    public static function ckTagBanOne(string $type): string
    {
        return 'tag:py-system:ban-one-' . $type;
    }

    /**
     * Ip 范围
     * @param string $type
     * @return string
     */
    public static function ckTagBanIpRange(string $type): string
    {
        return 'tag:py-system:ban-ip-range-' . $type;
    }
}