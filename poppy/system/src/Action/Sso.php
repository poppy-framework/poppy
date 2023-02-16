<?php

declare(strict_types = 1);

namespace Poppy\System\Action;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Events\PamLogoutEvent;
use Poppy\System\Events\PamSsoEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamToken;
use Poppy\System\Models\SysConfig;
use Request;
use Throwable;

/**
 * 单点登录
 */
class Sso
{
    use AppTrait;

    public const SSO_NONE       = 'none';
    public const SSO_GROUP      = 'group';
    public const SSO_DEVICE_NUM = 'device_num';

    public const GROUP_UNLIMITED = 'unlimited';
    public const GROUP_KICKED    = 'kicked';

    private array $groups = [
        'app:' . self::GROUP_KICKED    => ['android', 'ios'],
        'web:' . self::GROUP_UNLIMITED => ['h5', 'webapp'],
    ];

    public function __construct()
    {
        // 自定义的分组覆盖系统默认分组
        if (config('poppy.system.sso_group')) {
            $this->groups = config('poppy.system.sso_group');
        }
    }

    /**
     * @param PamAccount $pam
     * @param string     $device_id   设备 ID
     * @param string     $device_type 设备类型
     * @param string     $token       token
     * @return bool
     * @throws Exception
     */
    public function handle(PamAccount $pam, string $device_id, string $device_type, string $token): bool
    {
        $ssoType      = (string) sys_setting('py-system::pam.sso_type');
        $maxDeviceNum = (int) (sys_setting('py-system::pam.sso_device_num') ?: 10);
        // 不启用
        if (!self::isEnable()) {
            return true;
        }

        // 不对空 os 进行拦截
        $emptyHold = (string) sys_setting('py-system::pam.sso_os_empty_hold') ?: SysConfig::STR_NO;
        if (!$device_type && $emptyHold === SysConfig::STR_NO) {
            return true;
        }

        // 设备数据限制
        if (!$device_id || !$device_type) {
            return $this->setError('开启单一登录必须传递设备ID/设备类型');
        }

        // 设备标识限制
        $devices = Arr::flatten($this->groups);
        if (!in_array($device_type, $devices, true)) {
            return $this->setError('设备类型必须是' . implode(',', $devices) . '中的一种');
        }

        // 分组不设限
        $groupType = $this->groupType($device_type);
        if ($groupType === self::GROUP_UNLIMITED) {
            return true;
        }


        $tokenMd5  = md5($token);
        $pamId     = $pam->id;
        $expiredAt = Carbon::now()->addMinutes(config('jwt.ttl'));

        $logoutUsers = collect();
        switch ($ssoType) {
            // 保留最多 10 个设备, 允许同时登录, 记录设备信息, 同时登录数量受{最大设备数量}限制
            case self::SSO_DEVICE_NUM:
                $num = PamToken::where('account_id', $pamId)->count();
                if ($num >= $maxDeviceNum) {
                    // 根据设备时间/数量倒排删除
                    $logoutUsers = PamToken::where('account_id', $pamId)
                        ->where('device_id', '!=', $device_id)
                        ->orderBy('id')
                        ->limit(($num - $maxDeviceNum) + 1)
                        ->get();
                }
                break;
            case self::SSO_GROUP:
                // 同组内进行互踢
                if ($groupType === self::GROUP_KICKED) {
                    // 查询同组的设备类型
                    $total = [];
                    foreach ($this->groups as $group) {
                        if (in_array($device_type, $group, true)) {
                            $total = $group;
                        }
                    }
                    // 删除同组内其他设备
                    $logoutUsers = PamToken::where('account_id', $pam->id)
                        ->where('device_id', '!=', $device_id)
                        ->whereIn('device_type', $total)->get();
                }
                break;
        }

        // 触发数据的删除和事件, 事件用于通知用户下线
        if ($logoutUsers->count()) {
            PamToken::whereIn('id', $logoutUsers->pluck('id')->toArray())->delete();
            event(new PamSsoEvent($pam, $logoutUsers));
        }

        // 创建/更新用户的设备类型
        /** @var PamToken $current */
        PamToken::updateOrInsert([
            'account_id' => $pamId,
            'device_id'  => $device_id,
        ], [
            'token_hash'  => $tokenMd5,
            'device_type' => $device_type,
            'expired_at'  => $expiredAt->toDateTimeString(),
            'login_ip'    => Request::ip(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        $this->validateUser($pamId);
        return true;
    }

    /**
     * 初始化, 当关闭的时候, 清除数据
     * 开启的时候, 数据遵循自由变更, 不对数据进行额外的处理
     * @throws Exception
     */
    public function init(): void
    {
        if (!self::isEnable()) {
            PamToken::whereKeyNot(0)->delete();
            RdsDb::instance()->del(PySystemDef::ckTagSsoValid());
        }
    }

    /**
     * 使用户可用
     * @param $pamId
     * @return void
     */
    public function validateUser($pamId): void
    {
        $Rds  = RdsDb::instance();
        $data = $this->userTokenData($pamId);
        $Rds->hSet(PySystemDef::ckTagSsoValid(), $pamId, $data);
    }

    /**
     * 禁用用户和 token
     * @param int $pamId
     * @return void
     * @throws Exception
     */
    public function banUser(int $pamId): void
    {
        $Rds = RdsDb::instance();
        PamToken::where('account_id', $pamId)->delete();
        // delete from key
        $Rds->hDel(PySystemDef::ckTagSsoValid(), $pamId);
    }


    /**
     * 根据 Token 禁用并移除 Token
     * @param PamToken $pt
     * @param bool     $delete
     * @return void
     * @throws Exception
     */
    public function banToken(PamToken $pt, bool $delete = true): void
    {
        $Rds = RdsDb::instance();
        // delete from key
        $tokens = $Rds->hGet(PySystemDef::ckTagSsoValid(), $pt->account_id);
        if (is_array($tokens) && count($tokens) && isset($tokens[$pt->token_hash])) {
            unset($tokens[$pt->token_hash]);
            if (count($tokens)) {
                $Rds->hSet(PySystemDef::ckTagSsoValid(), $pt->account_id, $tokens);
            }
            else {
                $Rds->hDel(PySystemDef::ckTagSsoValid(), $pt->account_id);
            }
        }

        if ($delete) {
            $pt->delete();
        }
    }


    /**
     * @return int
     * @throws Exception
     */
    public function clearExpired(): int
    {
        $tokens = PamToken::where('expired_at', '<', Carbon::now()->toDateTimeString())->get();

        $tokens->each(function (PamToken $pt) {
            $this->banToken($pt, false);
        });

        // 批量删除
        PamToken::where('expired_at', '<', Carbon::now()->toDateTimeString())->delete();

        return $tokens->count();
    }

    /**
     * SSO 退出登录
     * @param int    $id    用户 ID
     * @param string $token JWT Token
     * @return bool
     * @throws Throwable
     */
    public function logout(int $id, string $token): bool
    {
        $tokenHash = md5($token);

        $pt = PamToken::where('token_hash', $tokenHash)->first();

        if ($pt) {
            $this->banToken($pt);

            event(new PamLogoutEvent($id, $pt));
        }
        return true;
    }

    /**
     * 是否启用 sso 登录
     * @return bool
     */
    public static function isEnable(): bool
    {
        $ssoType = (string) sys_setting('py-system::pam.sso_type');
        return !($ssoType === '' || $ssoType === self::SSO_NONE);
    }

    /**
     * @param string|null $key          Key
     * @param bool        $check_exists 检测键值是否存在
     * @return array|string
     */
    public static function kvType(string $key = null, bool $check_exists = false)
    {
        $desc = [
            self::SSO_NONE       => '不启用',
            self::SSO_DEVICE_NUM => '数量限制模式',
            self::SSO_GROUP      => '分组模式',
        ];
        return kv($desc, $key, $check_exists);
    }

    /**
     * 返回组说明
     * @return array|string
     */
    public function groupDesc($str = false)
    {
        $groups = [];
        if ($str) {
            foreach ($this->groups as $gk => $group) {
                $deviceTypes = implode(',', $group);
                $groups[]    = "{$gk}({$deviceTypes})";
            }
            return implode(', ', $groups);
        }
        return $this->groups;
    }

    /**
     * 是否 OS 不设限
     * @param string $os
     * @return string
     */
    public function groupType(string $os): string
    {
        $name = '';
        foreach ($this->groups as $gk => $group) {
            if (in_array($os, $group, true)) {
                $name = $gk;
            }
        }
        if (Str::contains($name, self::GROUP_UNLIMITED)) {
            return self::GROUP_UNLIMITED;
        }
        if (Str::contains($name, self::GROUP_KICKED)) {
            return self::GROUP_KICKED;
        }
        return '';
    }

    /**
     * @param $account_id
     * @return array{data: array, expired:array}
     */
    private function userTokenData($account_id): array
    {
        $tokens = PamToken::where('account_id', $account_id)->get();
        $data   = [];
        $tokens->each(function (PamToken $pt) use (&$data) {
            $data[$pt->token_hash] = "{$pt->device_type}|{$pt->expired_at}|{$pt->id}";
        });
        return $data;
    }
}