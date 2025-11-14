<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerSetting extends Model
{
    //
    protected $fillable = [
      'key',
      'name',
      'mode',
      'value',
      'unit',
      'time_details',
      'is_active',
    ];

    protected $casts = [
        'time_details' => 'array',
        'is_active' => 'boolean',
    ];
}
