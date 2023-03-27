<?php

declare(strict_types = 1);

namespace Poppy\Content\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;

/**
 * \Poppy\Content\Models\SysContent
 *
 * @mixin Eloquent
 * @property int         $id
 * @property string      $title      标题
 * @property string      $type       分类[简易分类标识]
 * @property int         $cat_id     分类 ID
 * @property string      $thumb      缩略图
 * @property int         $list_order 排序
 * @property int         $is_enable  是否启用
 * @property string      $content    内容
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SysContent filter(array $input = [], $filter = null)
 * @method static Builder|SysContent newModelQuery()
 * @method static Builder|SysContent newQuery()
 * @method static Builder|SysContent pageFilter(PageInfo $pageInfo)
 * @method static Builder|SysContent paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysContent query()
 * @method static Builder|SysContent simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysContent whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysContent whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysContent whereLike($column, $value, $boolean = 'and')
 */
class SysContent extends Model
{
    use FilterTrait;

    public const TYPE_DEFAULT = 'default';

    protected $table = 'sys_content';

    protected $fillable = [
        'title',
        'type',
        'thumb',
        'list_order',
        'content',
    ];

    public static function kvType(): array
    {
        $default = [
            [
                'type'  => self::TYPE_DEFAULT,
                'title' => '默认',
            ],
        ];
        return collect(array_merge(config('poppy.content.types', []), $default))
            ->pluck('title', 'type')->toArray();
    }
}
