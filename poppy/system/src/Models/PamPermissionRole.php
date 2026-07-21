<?php

declare(strict_types = 1);

namespace Poppy\System\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 角色 & 权限表
 *
 * @property int $permission_id
 * @property int $role_id
 *
 * @method static Builder|PamPermissionRole newModelQuery()
 * @method static Builder|PamPermissionRole newQuery()
 * @method static Builder|PamPermissionRole query()
 *
 * @mixin Eloquent
 */
class PamPermissionRole extends Model
{
    public $timestamps = false;

    protected $table = 'pam_permission_role';

    protected $fillable = [
        'permission_id',
        'role_id',
    ];
}
