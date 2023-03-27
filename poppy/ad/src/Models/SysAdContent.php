<?php

declare(strict_types = 1);

namespace Poppy\Ad\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;

/**
 * 广告内容
 *
 * @mixin Eloquent
 * @property int         $id         id
 * @property string      $title      广告标题
 * @property int         $place_id   广告位ID
 * @property string      $value      动作值
 * @property string      $introduce  广告的介绍
 * @property string|null $end_at     开始时间
 * @property string|null $start_at   开始时间
 * @property string      $note       备注
 * @property string      $action     动作
 * @property int         $list_order 排序
 * @property int         $is_enable  是否启用[0: 不显示, 1:显示]
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SysAdContent filter(array $input = [], $filter = null)
 * @method static Builder|SysAdContent newModelQuery()
 * @method static Builder|SysAdContent newQuery()
 * @method static Builder|SysAdContent pageFilter(PageInfo $pageInfo)
 * @method static Builder|SysAdContent paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysAdContent query()
 * @method static Builder|SysAdContent simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysAdContent whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysAdContent whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysAdContent whereLike($column, $value, $boolean = 'and')
 */
class SysAdContent extends Model
{
    use FilterTrait;

    public const ACTION_ROUTE = 'route';
    public const ACTION_URL   = 'url';
    public const ACTION_NONE  = 'none';

    protected $table = 'sys_ad_content';

    protected $fillable = [
        'title',
        'place_id',
        'src',
        'introduce',
        'end_at',
        'start_at',
        'action',
        'value',
        'list_order',
        'is_enable',
    ];

    /**
     * 动作
     * @param null $key key
     * @return array|string
     */
    public static function kvAction($key = null)
    {
        $desc = [
            self::ACTION_ROUTE => '内链',
            self::ACTION_URL   => '链接',
            self::ACTION_NONE  => '无操作',
        ];

        return kv($desc, $key);
    }
}
