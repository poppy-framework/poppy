<?php

declare(strict_types = 1);

namespace Poppy\App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Poppy\App\Classes\AppDef;
use Poppy\App\Exceptions\AppNotExistsException;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;
use Poppy\System\Models\SysConfig;

/**
 * 应用管理
 *
 * @property int         $id             应用 ID
 * @property string      $title          应用名称
 * @property string      $name           应用标识
 * @property string      $secret         应用密钥
 * @property int         $account_id     账号 ID
 * @property string      $account_type   账号用户类型
 * @property string      $note           应用备注
 * @property int         $is_enable      是否启用
 * @property string      $permissions    权限
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SysApp filter(array $input = [], $filter = null)
 * @method static Builder|SysApp newModelQuery()
 * @method static Builder|SysApp newQuery()
 * @method static Builder|SysApp pageFilter(PageInfo $pageInfo)
 * @method static Builder|SysApp paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysApp query()
 * @method static Builder|SysApp simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysApp whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysApp whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysApp whereLike($column, $value, $boolean = 'and')
 * @mixin Eloquent
 */
class SysApp extends Model
{
    use FilterTrait;

    protected $table = 'sys_app';

    protected $fillable = [
        'title',
        'name',
        'account_id',
        'account_type',
        'secret',
        'permissions',
        'note',
    ];

    /**
     * 获取缓存的信息
     * @param int $appid
     * @return array
     * @throws AppNotExistsException
     */
    public static function item(int $appid): array
    {
        return sys_tag('py-app')->remember(AppDef::ckItem($appid), SysConfig::MIN_ONE_MONTH, function () use ($appid) {
            $app = self::find($appid);
            if (!$app) {
                throw new AppNotExistsException("应用 {$appid} 不存在!");
            }
            return $app->toArray();
        });
    }

    /**
     * 验证权限
     * @param int    $appid
     * @param string $permission
     * @return bool
     * @throws AppNotExistsException
     */
    public static function check(int $appid, string $permission): bool
    {
        $item = self::item($appid);
        $pk   = array_flip($item['permissions']);
        return isset($pk[$permission]);

    }

    public function setPermissionsAttribute(array $permissions): void
    {
        $this->attributes['permissions'] = implode(',', $permissions);
    }

    public function getPermissionsAttribute(string $permissions)
    {
        return explode(',', $permissions);
    }
}
