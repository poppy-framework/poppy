<?php

declare(strict_types = 1);

namespace Poppy\Ad\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;

/**
 * 广告内容
 *
 * @property int         $id               id
 * @property string      $title            广告标题
 * @property int         $place_id         广告位ID
 * @property string      $url              链接地址
 * @property string      $introduce        广告的介绍
 * @property string|null $end_at           结束时间
 * @property string|null $start_at         开始时间
 * @property string      $note             备注
 * @property string      $text_name        文字广告名称
 * @property string      $text_url         文字URL
 * @property string      $text_title       文字广告title标题
 * @property string      $text_style       文字广告的颜色
 * @property string      $image_src        图片广告的图片地址
 * @property string      $image_url        图片广告链接地址
 * @property string      $flash_src        flash地址
 * @property string      $flash_url        flash链接地址
 * @property string      $action           动作
 * @property string      $action_value     动作值
 * @property int         $flash_loop       flash循环次数
 * @property int         $list_order       排序
 * @property int         $status           0: 不显示, 1:显示
 * @property Carbon|null $created_at       创建时间
 * @property Carbon|null $updated_at       修改时间
 * @method static Builder|SysAdContent filter($input = [], $filter = null)
 * @method static Builder|SysAdContent pageFilter(PageInfo $pageInfo)
 * @method static Builder|SysAdContent paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysAdContent simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysAdContent whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysAdContent whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysAdContent whereLike($column, $value, $boolean = 'and')
 * @mixin Eloquent
 */
class SysAdContent extends Model
{
    use FilterTrait;

    const STATUS_YES = 1;
    const STATUS_NO  = 0;

    const ACTION_HUNTER_CENTER = 'hunter_center';
    const ACTION_H5_LINK       = 'h5_link';
    const ACTION_NO_CLICK      = 'no_click';
    const TOPIC                = 'topic';

    protected $table = 'sys_ad_content';

    protected $fillable = [
        'title',
        'place_id',
        'url',
        'introduce',
        'end_at',
        'start_at',
        'note',
        'text_name',
        'text_url',
        'text_title',
        'text_style',
        'image_src',
        'image_url',
        'flash_src',
        'flash_url',
        'action',
        'action_value',
        'flash_loop',
        'list_order',
        'status',
    ];

    /**
     * 显示状态
     * @param null $key key
     * @return array|string
     */
    public static function kvStatus($key = null)
    {
        $desc = [
            self::STATUS_YES => '显示',
            self::STATUS_NO  => '不显示',
        ];

        return kv($desc, $key);
    }

    /**
     * 动作
     * @param null $key key
     * @return array|string
     */
    public static function kvAction($key = null)
    {
        $desc = [
            self::ACTION_HUNTER_CENTER => '大神中心',
            self::ACTION_H5_LINK       => 'H5链接',
            self::ACTION_NO_CLICK      => '不能点击',
            self::TOPIC                => '圈子广告',
        ];

        return kv($desc, $key);
    }
}
