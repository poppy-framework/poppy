<?php

declare(strict_types = 1);

namespace Poppy\Content\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Poppy\Framework\Http\Pagination\PageInfo;
use Poppy\System\Classes\Traits\FilterTrait;
use Poppy\System\Models\PamAccount;

/**
 * \Poppy\Content\Models\SysContent
 *
 * @mixin Eloquent
 * @property int                  $id
 * @property string               $title      标题
 * @property string               $slug       友好访问名称
 * @property string               $type       分类[简易分类标识]
 * @property int                  $cat_id     分类 ID
 * @property string               $thumb      缩略图
 * @property int                  $list_order 排序
 * @property int                  $account_id 账号 ID
 * @property int                  $is_enable  是否启用
 * @property string               $content    内容
 * @property string               $author     作者
 * @property string               $create_at  创建时间(展示用)
 * @property Carbon|null          $created_at
 * @property Carbon|null          $updated_at
 * @property-read PamAccount|null $pam
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
        'slug',
        'type',
        'thumb',
        'list_order',
        'account_id',
        'cat_id',
        'content',
        'author',
        'create_at',
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

    /**
     * 返回用户
     * @return HasOne
     */
    public function pam(): HasOne
    {
        return $this->hasOne(PamAccount::class, 'id', 'account_id');
    }
}
