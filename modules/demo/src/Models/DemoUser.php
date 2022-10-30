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
class DemoUser extends Model
{
    protected $table = 'demo_user';

    public $timestamps = false;

    protected $fillable = [
        // fillable
    ];
}