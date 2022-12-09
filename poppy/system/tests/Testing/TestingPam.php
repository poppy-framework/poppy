<?php

declare(strict_types = 1);

namespace Poppy\System\Tests\Testing;

use Poppy\Framework\Helper\StrHelper;
use Poppy\System\Models\PamAccount;

/**
 * 随机获取数据
 */
class TestingPam
{

    public static function backend()
    {
        return PamAccount::passport(env('TESTING_BACKEND'));
    }

    /**
     * 获取随机用户名
     * @param bool $is_register 是否已经注册
     * @return mixed
     */
    public static function username(bool $is_register = true)
    {
        $Db = PamAccount::inRandomOrder();
        if ($is_register) {
            $Db->where('password', '!=', '');
        }
        else {
            $Db->where('password', '=', '');
        }

        return $Db->value('username');
    }

    /**
     * 获取随机AccountId
     * @param bool $is_register 是否已经注册
     * @return int
     */
    public static function id(bool $is_register = true): int
    {
        $Db = PamAccount::inRandomOrder();
        if ($is_register) {
            $Db->where('password', '!=', '');
        }
        else {
            $Db->where('password', '=', '');
        }

        return $Db->value('id');
    }

    /**
     * 获取随机账号
     * @return PamAccount
     */
    public static function randUser(): PamAccount
    {
        $Db = PamAccount::where('type', PamAccount::TYPE_USER)->inRandomOrder();
        return $Db->first();
    }

    /**
     * 获取随机后台账号
     * @return PamAccount
     */
    public static function randBackend(): PamAccount
    {
        $Db = PamAccount::where('type', PamAccount::TYPE_BACKEND)->inRandomOrder();
        return $Db->first();
    }

    /**
     * 除去测试用户
     * @return array
     */
    public static function exclude(): array
    {
        $users = StrHelper::separate(PHP_EOL, (string) sys_setting('py-system::testing.users'));
        if (!$users) {
            return [];
        }

        return PamAccount::whereIn('mobile', $users)->pluck('id')->toArray();
    }
}
