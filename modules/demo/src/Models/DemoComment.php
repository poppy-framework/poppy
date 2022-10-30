<?php

namespace Demo\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @method static Builder|DemoWebapp newModelQuery()
 * @method static Builder|DemoWebapp newQuery()
 * @method static Builder|DemoWebapp query()
 * @mixin Eloquent
 */
class DemoComment extends Model
{
    protected $table = 'demo_comment';

    public $timestamps = false;

    protected $fillable = [
        // fillable
    ];
}