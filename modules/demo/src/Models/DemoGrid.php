<?php

declare(strict_types = 1);

namespace Demo\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Poppy\System\Models\PamAccount;

/**
 * \Demo\Models\DemoGrid
 *
 * @mixin Eloquent
 * @property int         $id
 * @property string|null $birth_date
 * @property string|null $post_at
 * @property string|null $title
 * @property string|null $setting
 * @property string|null $content
 * @property string|null $email
 * @property string|null $truename
 * @property int|null    $age
 * @property int|null    $score
 * @property int         $sort
 * @property int|null    $status
 * @property int|null    $progress
 * @property int|null    $is_enable
 * @property string|null $file
 * @property string|null $files
 * @property string|null $video
 * @property string|null $pdf
 * @property string|null $audio
 * @property string|null $image
 * @property string|null $images
 * @property string|null $link
 * @property int|null    $account_id
 * @method static Builder|DemoGrid newModelQuery()
 * @method static Builder|DemoGrid newQuery()
 * @method static Builder|DemoGrid query()
 */
class DemoGrid extends Model
{
    public $timestamps = false;

    protected $table = 'demo_grid';

    protected $fillable = [
        'title',
    ];

    protected $casts = [
        'setting',
    ];


    public function pam(): HasOne
    {
        return $this->hasOne(PamAccount::class, 'id', 'account_id');
    }

    public static function kvStatus($key = null)
    {
        $defs = [
            1 => '未发布',
            2 => '草稿',
            5 => '待审核',
            3 => '已发布',
            4 => '已删除',
        ];
        return kv($defs, $key);
    }

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}