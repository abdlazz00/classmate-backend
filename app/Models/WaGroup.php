<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaGroup extends Model
{
    //
    protected $fillable=[
        'name',
        'class_name',
        'group_code',
        'is_active',
    ];
}
