<?php

declare(strict_types = 1);

namespace Poppy\Category\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Poppy\Framework\Helper\TreeHelper;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;

/**
 * 分类管理
 * @property int         $id         id
 * @property string      $title      标题
 * @property string      $parent_id  上级 ID
 * @property string      $type       类型
 * @property string      $list_order 排序
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 修改时间
 * @method static Builder|SysCategory filter($input = [], $filter = null)
 * @method static Builder|SysCategory pageFilter(PageInfo $pageInfo)
 * @method static Builder|SysCategory paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysCategory simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|SysCategory whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysCategory whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|SysCategory whereLike($column, $value, $boolean = 'and')
 * @mixin Eloquent
 */
class SysCategory extends Model
{
    use FilterTrait;

    const TYPE_DEFAULT = 'default';

    const POSITION_BEFORE = 'before';
    const POSITION_AFTER  = 'after';

    protected $table = 'sys_category';

    protected $fillable = [
        'id',
        'title',
        'type',
        'list_order',
        'parent_id',
        'top_id',
    ];

    public static function kvType(): array
    {
        $default = [
            [
                'type'  => self::TYPE_DEFAULT,
                'title' => '默认',
            ],
        ];
        return collect(array_merge(config('poppy.category.types', []), $default))
            ->pluck('title', 'type')->toArray();
    }

    /**
     * 树型
     * @param string $type          类型
     * @param bool   $replace_space 空格
     * @return array
     */
    public static function tree(string $type, bool $replace_space = false): array
    {
        $categories = self::select(['id', 'title', 'parent_id'])
            ->where('type', $type)
            ->orderBy('list_order', 'desc')
            ->get()->keyBy('id')->toArray();

        $Tree = new TreeHelper();
        if ($replace_space) {
            $Tree->replaceSpace();
        }
        $Tree->init($categories, 'id', 'parent_id', 'title');
        return $Tree->getTreeArray(0);
    }
}
