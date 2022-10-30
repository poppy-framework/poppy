<?php

namespace Demo\Models;

use Illuminate\Database\Eloquent\Model;

class DemoDb extends Model
{
    protected $table = 'demo_db';

    protected $fillable = [
        'tiny_integer',
        'u_integer',
        'var_char_20',
        'char_20',
        'text',
        'decimal',
    ];

}
