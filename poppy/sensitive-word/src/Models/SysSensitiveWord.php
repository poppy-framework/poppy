<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * 敏感词表
 * @property int    $id
 * @property string $word      文字
 * @mixin Eloquent
 */
class SysSensitiveWord extends Model
{

    public $timestamps = false;

    protected $table = 'sys_sensitive_word';

    protected $fillable = [
        'word',
    ];
}