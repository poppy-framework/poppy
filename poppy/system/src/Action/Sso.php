<?php

declare(strict_types = 1);

namespace Poppy\System\Action;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Poppy\Core\Redis\RdsDb;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\System\Classes\PySystemDef;
use Poppy\System\Events\PamLogoutEvent;
use Poppy\System\Events\PamSsoEvent;
use Poppy\System\Models\PamAccount;
use Poppy\System\Models\PamToken;
use Request;
use Throwable;

/**
 * 单点登录
 */
class Sso
{
    use AppTrait;

    public const SSO_NONE   = 'none';
    public const SSO_ALL    = 'all';
    public const SSO_SINGLE = 'single';
    public const SSO_GROUP  = 'group';
    public const SSO_DEVICE = 'device';


    private array $groups = [
        'app' => ['android', 'ios'],
        'web' => ['h5', 'webapp', 'mp'],
        'pc'  => ['mac', 'linux', 'win'],
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

        // 启用
        if (!$device_id || !$device_type) {
            return $this->setError('开启单一登录必须传递设备ID/设备类型');
        }

        $devices = Arr::flatten($this->groups);
        if (!in_array($device_type, $devices, true)) {
            return $this->setError('设备类型必须是' . implode(',', $devices) . '中的一种');
        }

        $tokenMd5  = md5($token);
        $pamId     = $pam->id;
        $expiredAt = Carbon::now()->addMinutes(config('jwt.ttl'));

        $logoutUsers = collect();
        switch ($ssoType) {
            case self::SSO_ALL:
                // 保留最多 10 个设备, 允许同时登录, 记录设备信息, 同时登录数量受{最大设备数量}限制
                // 这里需要配上用户的设备管理, 自动
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
            case self::SSO_DEVICE:
                // 单端登录, 只移除当前类型设备[去除当前设备]
                $logoutUsers = PamToken::where('account_id', $pam->id)
                    ->where('device_id', '!=', $device_id)
                    ->where('device_type', $device_type)->get();
                break;
            case self::SSO_SINGLE:
                // 单点登录(Sso), 仅保留一台设备
                $logoutUsers = PamToken::where('account_id', $pam->id)
                    ->where('device_id', '!=', $device_id)
                    ->get();
                break;
            case self::SSO_GROUP:
                // 同组内登录
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
            self::SSO_NONE   => '不启用',
            self::SSO_SINGLE => '单点登录(Sso), 仅允许一端登录',
            self::SSO_GROUP  => '同组内单点登录. 各组之间允许同时登录',
            self::SSO_DEVICE => '单端登录, 同类型互踢, 不同设备类型可同时在线',
            self::SSO_ALL    => '允许同时登录, 记录设备信息, 同时登录数量受 {最大设备数量} 限制',
        ];
        return kv($desc, $key, $check_exists);
    }

    /**
     * 返回组说明
     * @return array|string
     */
    public function getGroups($str = false)
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