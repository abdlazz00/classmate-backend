<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogReminder extends Model
{
    //
    protected $fillable=[
        'assignment_id',
        'group_name',
        'message',
        'sent_at',
        'status',
    ];
    protected $casts=[
      'sent_at'=>'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
