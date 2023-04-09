<?php

declare(strict_types = 1);

namespace Poppy\Ad\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;
use Poppy\System\Models\SysConfig;

/**
 * 广告位
 *
 * @property int         $id         id
 * @property string      $title      广告位名称
 * @property string      $thumb      广告位示意图
 * @property string      $introduce  广告位介绍
 * @property int         $width      宽度
 * @property int         $height     高度
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 修改时间
 * @method static Builder|SysAdPlace filter($input = [], $filter = null)
 * @method static Builder|SysAdPlace pageFilter(PageInfo $pageInfo)
 * @method static Builder|SysAdPlace paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysAdPlace simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysAdPlace whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysAdPlace whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysAdPlace whereLike($column, $value, $boolean = 'and')
 * @mixin Eloquent
 */
class SysAdPlace extends Model
{
    use FilterTrait;

    protected $table = 'sys_ad_place';

    protected $fillable = [
        'id',
        'title',
        'thumb',
        'introduce',
        'width',
        'height',
    ];

    /**
     * 获取广告位标题
     * @return array
     */
    public static function title(): array
    {
        $result = [];
        $places = self::select(['id', 'title'])->get()->toArray();

        if (!$places) {
            return $result;
        }

        foreach ($places as $place) {
            $result[$place['id']] = $place['title'];
        }

        return $result;
    }

    /**
     * 通过广告位id获取广告位内容
     * @param int $id id
     * @return array
     */
    public static function returnAdContent(int $id): array
    {
        return SysAdContent::where('place_id', $id)
            ->orderBy('list_order')
            ->where('status', SysConfig::YES)->select(['id', 'src', 'action', 'value', 'title'])->get()->toArray();
    }
}
