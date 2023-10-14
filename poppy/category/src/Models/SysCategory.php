<?php

declare(strict_types = 1);

namespace Poppy\Category\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Poppy\Category\Classes\PyCategoryDef;
use Poppy\Framework\Helper\TreeHelper;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;
use Poppy\System\Models\SysConfig;

/**
 * 分类管理
 * @property int         $id         id
 * @property string      $title      标题
 * @property string      $name       标识
 * @property string      $parent_id  上级 ID
 * @property string      $type       类型
 * @property int         $list_order 排序
 * @property int         $is_enable  排序
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

    public const TYPE_DEFAULT = 'default';

    public const SORT_GT = 'gt';
    public const SORT_LT = 'lt';

    protected $table = 'sys_category';

    protected $fillable = [
        'title',
        'type',
        'name',
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
     * 名称和 ID 的映射
     * @param string $key
     * @return int
     */
    public static function kvNameRefId(string $key): int
    {
        if ($ref = sys_tag('py-category')->hGetAll(PyCategoryDef::ckNameRefKey())) {
            return $ref[$key] ?? 0;
        }
        $ref = self::where('name', '!=', '')->selectRaw("CONCAT(type, '-', name) as tn,id")->pluck('id', 'tn')->toArray();
        sys_tag('py-category')->hMSet(PyCategoryDef::ckNameRefKey(), $ref);
        return $ref[$key] ?? 0;
    }


    /**
     * ID 和 标题的映射
     * @param int $id
     * @return string
     */
    public static function kvTitle(int $id): string
    {
        if ($ref = sys_tag('py-category')->hGetAll(PyCategoryDef::ckIdRefTitle())) {
            return $ref[$id] ?? '';
        }
        $ref = self::pluck('title', 'id')->toArray();
        sys_tag('py-category')->hMSet(PyCategoryDef::ckIdRefTitle(), $ref);
        return $ref[$id] ?? '';
    }

    /**
     * ID 和 Name 的映射
     * @param int $id
     * @return string
     */
    public static function kvSlug(int $id): string
    {
        if ($ref = sys_tag('py-category')->hGetAll(PyCategoryDef::ckIdRefName())) {
            return $ref[$id] ?? (string) $id;
        }
        $ref = self::where('name', '!=', '')->selectRaw('name as tn,id')->pluck('tn', 'id')->toArray();
        sys_tag('py-category')->hMSet(PyCategoryDef::ckIdRefName(), $ref);
        return $ref[$id] ?? (string) $id;
    }

    /**
     * ID 和 Name 的映射
     * @param int $id
     * @return string
     */
    public static function kvTypeSlug(int $id): string
    {
        if ($ref = sys_tag('py-category')->hGetAll(PyCategoryDef::ckIdRefName())) {
            return $ref[$id] ?? (string) $id;
        }
        $ref = self::where('name', '!=', '')->selectRaw("CONCAT(type, '-', name) as tn,id")->pluck('tn', 'id')->toArray();
        sys_tag('py-category')->hMSet(PyCategoryDef::ckIdRefName(), $ref);
        return $ref[$id] ?? (string) $id;
    }

    /**
     * 树型
     * @param string $type 类型
     * @param bool   $replace_space 空格
     * @return array
     */
    public static function tree(string $type, bool $replace_space = false): array
    {
        $categories = self::select(['id', 'title', 'parent_id'])
            ->where('type', $type)
            ->where('is_enable', SysConfig::ENABLE)
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
